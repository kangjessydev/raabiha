<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLog;
use App\Models\Cashflow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class PosTransactionService
{
    /**
     * Memproses pesanan POS dengan perhitungan harga server-side,
     * lock stock, pengurangan stock, dan pencatatan arus kas.
     */
    public function completePosTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'];
            $cashierId = $data['cashier_id'];
            $posSessionId = $data['pos_session_id'];
            $cashPaid = $data['cash_paid'] ?? 0;
            $paymentDetails = $data['payment_details'] ?? null;
            $customerName = $data['customer_name'] ?? null;
            $customerPhone = $data['customer_phone'] ?? null;

            $subtotal = 0;
            $totalPurchasePrice = 0;
            $orderItemsData = [];

            // 1. Validasi dan kunci stok, hitung harga
            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                
                if (isset($item['product_variant_id']) && $item['product_variant_id']) {
                    $lockedVariant = ProductVariant::where('id', $item['product_variant_id'])
                        ->lockForUpdate()
                        ->first();
                        
                    if (!$lockedVariant) {
                        throw new Exception("Varian produk tidak ditemukan.");
                    }
                    if ($lockedVariant->stock < $qty) {
                        throw new Exception("Stok tidak cukup untuk varian: " . $lockedVariant->name);
                    }

                    $lockedProduct = Product::find($lockedVariant->product_id);
                    $price = $lockedVariant->pos_price ?: ($lockedVariant->price ?: $lockedProduct->price);
                    // Gunakan harga diskon jika ada
                    $finalPrice = $lockedVariant->pos_discount_price ?: $price;
                    $purchasePrice = $lockedVariant->purchase_price ?: $lockedProduct->purchase_price;
                    
                    $subtotal += ($finalPrice * $qty);
                    $totalPurchasePrice += ($purchasePrice * $qty);
                    
                    $orderItemsData[] = [
                        'type' => 'variant',
                        'product_id' => $lockedProduct->id,
                        'product_variant_id' => $lockedVariant->id,
                        'name' => $lockedProduct->name . ' - ' . $lockedVariant->name,
                        'price' => $finalPrice,
                        'quantity' => $qty,
                        'purchase_price' => $purchasePrice,
                        'locked_model' => $lockedVariant,
                    ];
                } else {
                    $lockedProduct = Product::where('id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();
                        
                    if (!$lockedProduct) {
                        throw new Exception("Produk tidak ditemukan.");
                    }
                    if ($lockedProduct->stock < $qty) {
                        throw new Exception("Stok tidak cukup untuk produk: " . $lockedProduct->name);
                    }

                    $price = $lockedProduct->pos_price ?: $lockedProduct->price;
                    $finalPrice = $lockedProduct->pos_discount_price ?: $price;
                    $purchasePrice = $lockedProduct->purchase_price;
                    
                    $subtotal += ($finalPrice * $qty);
                    $totalPurchasePrice += ($purchasePrice * $qty);
                    
                    $orderItemsData[] = [
                        'type' => 'product',
                        'product_id' => $lockedProduct->id,
                        'product_variant_id' => null,
                        'name' => $lockedProduct->name,
                        'price' => $finalPrice,
                        'quantity' => $qty,
                        'purchase_price' => $purchasePrice,
                        'locked_model' => $lockedProduct,
                    ];
                }
            }

            // Diskon manual
            $discount = (float) ($data['discount'] ?? 0);
            $grandTotal = $subtotal - $discount;
            if ($grandTotal < 0) $grandTotal = 0;
            $cashChange = $cashPaid - $grandTotal;

            // Validasi & Increment Voucher jika digunakan
            $voucherId = $data['voucher_id'] ?? null;
            if ($voucherId) {
                $voucher = \App\Models\Voucher::find($voucherId);
                if ($voucher) {
                    if ($voucher->max_uses && $voucher->used_count >= $voucher->max_uses) {
                        throw new Exception("Voucher {$voucher->name} sudah mencapai batas maksimum kuota penggunaan.");
                    }
                    $voucher->increment('used_count');
                }
            }

            $normalizedPhone = \App\Models\PosCustomer::normalizePhone($customerPhone) ?: $customerPhone;

            $paymentDetails = is_array($paymentDetails) ? $paymentDetails : (is_string($paymentDetails) ? json_decode($paymentDetails, true) : []);
            $paymentDetails['manual_discount'] = (float) ($data['manual_discount'] ?? 0);
            $paymentDetails['voucher_discount'] = (float) ($data['voucher_discount'] ?? 0);
            
            // 2. Buat Order (Gunakan placeholder sementara untuk menjamin keunikan ID)
            $order = Order::create([
                'order_number' => 'POS-TEMP-' . uniqid(),
                'user_id' => null, // Pembeli di toko fisik
                'customer_name' => $customerName,
                'customer_phone' => $normalizedPhone,
                'cashier_id' => $cashierId,
                'pos_session_id' => $posSessionId,
                'voucher_id' => $voucherId,
                'source' => 'pos', // Penting untuk guard observer
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'grand_total' => $grandTotal,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'cash_paid' => $cashPaid,
                'cash_change' => $cashChange,
                'payment_details' => json_encode($paymentDetails),
                'total_purchase_price' => $totalPurchasePrice,
                'is_dropship' => false,
            ]);

            // Generate Order Number resmi dari ID auto-increment yang dijamin unik
            $orderNumber = 'POS-' . date('Ymd') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);
            $order->update(['order_number' => $orderNumber]);

            // 3. Kurangi stok, catat Log, dan buat OrderItem
            foreach ($orderItemsData as $itemData) {
                $qty = $itemData['quantity'];
                $lockedModel = $itemData['locked_model'];
                
                $before = $lockedModel->stock;
                $lockedModel->decrement('stock', $qty);
                $after = $before - $qty;

                // Catat Log Stok
                StockLog::create([
                    'product_id' => $itemData['product_id'],
                    'product_variant_id' => $itemData['product_variant_id'],
                    'type' => 'out',
                    'quantity_before' => $before,
                    'quantity_change' => -$qty,
                    'quantity_after' => $after,
                    'reason' => 'Sales',
                    'notes' => 'Penjualan POS #' . $order->order_number,
                    'user_id' => $cashierId,
                ]);

                // Buat Order Item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'product_variant_id' => $itemData['product_variant_id'],
                    'name' => $itemData['name'],
                    'price' => $itemData['price'],
                    'quantity' => $qty,
                    'total' => $itemData['price'] * $qty,
                    'purchase_price' => $itemData['purchase_price'],
                ]);
            }

            // 4. Catat Cashflow otomatis (Karena observer dilewati)
            Cashflow::create([
                'transaction_date' => now()->toDateString(),
                'type' => 'in',
                'category' => 'pos_sale',
                'amount' => $grandTotal,
                'description' => 'Penjualan POS #' . $order->order_number,
                'order_id' => $order->id,
                'source' => 'pos',
                'is_reversed' => false,
            ]);

            // 5. Proses Loyalti Stempel Pelanggan POS
            $this->processPosLoyalty($order, $data);

            return $order;
        });
    }

    /**
     * Memproses perolehan & penukaran stempel loyalti POS
     */
    private function processPosLoyalty(Order $order, array $data): void
    {
        $enabled = filter_var(\App\Models\SiteSetting::where('key', 'pos_loyalty_enabled')->value('value') ?? true, FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) return;

        $phone = \App\Models\PosCustomer::normalizePhone($order->customer_phone);
        if (!$phone) return;

        $customer = \App\Models\PosCustomer::firstOrCreate(
            ['phone' => $phone],
            ['name' => $order->customer_name ?: 'Pelanggan POS']
        );

        if ($order->customer_name && $customer->name !== $order->customer_name) {
            $customer->update(['name' => $order->customer_name]);
        }

        // 1. Cek Masa Berlaku Stempel (Expiry)
        $expiryMonths = (int) (\App\Models\SiteSetting::where('key', 'pos_loyalty_stamp_expiry_months')->value('value') ?? 6);
        if ($expiryMonths > 0 && $customer->last_visit_at && $customer->stamp_count > 0) {
            $monthsSinceLastVisit = $customer->last_visit_at->diffInMonths(now());
            if ($monthsSinceLastVisit >= $expiryMonths) {
                $expiredStamps = $customer->stamp_count;
                $expiredPoints = $customer->points_balance;
                
                $customer->update([
                    'stamp_count'    => 0,
                    'points_balance' => 0,
                ]);

                \App\Models\PosStampLog::create([
                    'pos_customer_id' => $customer->id,
                    'order_id'        => $order->id,
                    'type'            => 'expired',
                    'stamps'          => -$expiredStamps,
                    'points'          => -$expiredPoints,
                    'description'     => "Stempel hangus karena tidak bertransaksi > {$expiryMonths} bulan.",
                ]);
            }
        }

        // 2. Proses Penukaran Stempel (Redemption if kasir redeemed tier voucher)
        $redeemedStamps = (int) ($data['loyalty_redeem_stamps'] ?? 0);

        if ($order->voucher_id && $redeemedStamps <= 0) {
            $tiersSetting = \App\Models\SiteSetting::where('key', 'pos_loyalty_tiers')->value('value');
            if ($tiersSetting) {
                $tiers = is_string($tiersSetting) ? json_decode($tiersSetting, true) : $tiersSetting;
                if (is_array($tiers)) {
                    foreach ($tiers as $tier) {
                        if (isset($tier['voucher_id']) && $tier['voucher_id'] == $order->voucher_id) {
                            $redeemedStamps = (int) ($tier['min_stamps'] ?? 0);
                            break;
                        }
                    }
                }
            }
        }

        $pointsRatio = (int) (\App\Models\SiteSetting::where('key', 'pos_loyalty_stamps_to_points_ratio')->value('value') ?? 10);

        if ($redeemedStamps > 0) {
            if ($customer->stamp_count < $redeemedStamps) {
                throw new \Exception("Stempel pelanggan tidak mencukupi untuk menukarkan voucher ini. Syarat: {$redeemedStamps} Cap, Saldo Pelanggan: {$customer->stamp_count} Cap.");
            }

            $redeemedPoints = $redeemedStamps * $pointsRatio;
            $customer->decrement('stamp_count', $redeemedStamps);
            $customer->decrement('points_balance', min($customer->points_balance, $redeemedPoints));
            $customer->refresh();

            \App\Models\PosStampLog::create([
                'pos_customer_id' => $customer->id,
                'order_id'        => $order->id,
                'type'            => 'redeemed',
                'stamps'          => -$redeemedStamps,
                'points'          => -$redeemedPoints,
                'description'     => "Penukaran {$redeemedStamps} Stempel untuk Voucher Reward POS.",
            ]);
        }

        // 3. Proses Perolehan Stempel Baru (Earning)
        $minSpend = (float) (\App\Models\SiteSetting::where('key', 'pos_loyalty_min_spend')->value('value') ?? 100000);
        
        if ($order->grand_total >= $minSpend) {
            // Mode kelipatan: per Rp 100k dapat 1 stempel, atau 1 stempel per transaksi
            $multiplierMode = filter_var(\App\Models\SiteSetting::where('key', 'pos_loyalty_multiplier_mode')->value('value') ?? false, FILTER_VALIDATE_BOOLEAN);
            $stampsEarned = $multiplierMode ? (int) floor($order->grand_total / $minSpend) : 1;
            
            if ($stampsEarned > 0) {
                $pointsEarned = $stampsEarned * $pointsRatio;

                $newStampCount = $customer->stamp_count + $stampsEarned;
                $completedCardsAdd = (int) floor($newStampCount / 9);
                $finalStampCount = $newStampCount % 9;

                $customer->update([
                    'stamp_count'           => $finalStampCount,
                    'points_balance'        => $customer->points_balance + $pointsEarned,
                    'total_stamps_earned'   => $customer->total_stamps_earned + $stampsEarned,
                    'completed_cards_count' => $customer->completed_cards_count + $completedCardsAdd,
                    'total_visits'          => $customer->total_visits + 1,
                    'total_spent'           => $customer->total_spent + $order->grand_total,
                    'last_visit_at'         => now(),
                ]);

                \App\Models\PosStampLog::create([
                    'pos_customer_id' => $customer->id,
                    'order_id'        => $order->id,
                    'type'            => 'earned',
                    'stamps'          => $stampsEarned,
                    'points'          => $pointsEarned,
                    'description'     => "Perolehan {$stampsEarned} Stempel ({$pointsEarned} Poin) dari Nota POS #{$order->order_number}.",
                ]);
            }
        } else {
            // Tetap update last_visit & total_spent walau tidak dapet stempel
            $customer->update([
                'total_visits'  => $customer->total_visits + 1,
                'total_spent'   => $customer->total_spent + $order->grand_total,
                'last_visit_at' => now(),
            ]);
        }
    }

    /**
     * Memproses retur fisik / penukaran barang POS
     */
    public function processPosReturn(array $data)
    {
        return DB::transaction(function () use ($data) {
            $orderId      = $data['order_id'];
            $cashierId    = $data['cashier_id'];
            $posSessionId = $data['pos_session_id'] ?? null;
            $type         = $data['type'] ?? 'exchange'; // 'exchange' or 'refund'
            $reason       = $data['reason'] ?? 'Tukar / Retur Barang POS';
            $returnedItemsPayload  = $data['returned_items'] ?? [];
            $exchangedItemsPayload = $data['exchanged_items'] ?? [];
            $refundPaymentMethod   = $data['refund_payment_method'] ?? 'cash';
            $refundBankName        = $data['refund_bank_name'] ?? null;
            $refundBankAccount     = $data['refund_bank_account'] ?? null;
            $supervisorId  = $data['supervisor_id'] ?? null;
            $supervisorPin = $data['supervisor_pin'] ?? null;

            $order = Order::with(['items'])->lockForUpdate()->find($orderId);
            if (!$order) {
                throw new Exception("Transaksi nota tidak ditemukan.");
            }
            if ($order->status === 'cancelled') {
                throw new Exception("Transaksi nota ini sudah dibatalkan (Void).");
            }
            if (empty($returnedItemsPayload)) {
                throw new Exception("Pilih minimal 1 barang yang akan dikembalikan / diretur.");
            }

            // 1. Hitung total retur dan validasi qty
            $returnedSubtotal = 0;
            $returnedItemsData = [];

            foreach ($returnedItemsPayload as $retItem) {
                $qty = (int) ($retItem['quantity'] ?? 0);
                if ($qty <= 0) continue;

                $orderItem = $order->items->first(function ($i) use ($retItem) {
                    return $i->product_id == $retItem['product_id'] && $i->product_variant_id == ($retItem['product_variant_id'] ?? null);
                });

                if (!$orderItem) {
                    throw new Exception("Barang yang diretur tidak ada di nota transaksi asli.");
                }

                $previouslyReturnedQty = \App\Models\PosReturnItem::whereHas('posReturn', function ($q) use ($order) {
                    $q->where('order_id', $order->id);
                })
                ->where('type', 'returned')
                ->where('product_id', $orderItem->product_id)
                ->where('product_variant_id', $orderItem->product_variant_id)
                ->sum('quantity');

                $availableToReturn = $orderItem->quantity - $previouslyReturnedQty;
                if ($qty > $availableToReturn) {
                    throw new Exception("Jumlah retur untuk {$orderItem->name} melebihi sisa nota ({$availableToReturn} unit).");
                }

                $itemPrice = $orderItem->price;
                $itemTotal = $itemPrice * $qty;
                $returnedSubtotal += $itemTotal;

                $returnedItemsData[] = [
                    'product_id'         => $orderItem->product_id,
                    'product_variant_id' => $orderItem->product_variant_id,
                    'name'               => $orderItem->name,
                    'quantity'           => $qty,
                    'price'              => $itemPrice,
                    'total'              => $itemTotal,
                ];
            }

            if (empty($returnedItemsData)) {
                throw new Exception("Kuantitas barang retur harus lebih besar dari 0.");
            }

            // 2. Hitung total penukaran (jika type == exchange)
            $exchangedSubtotal = 0;
            $exchangedItemsData = [];

            if ($type === 'exchange' && !empty($exchangedItemsPayload)) {
                foreach ($exchangedItemsPayload as $exItem) {
                    $qty = (int) ($exItem['quantity'] ?? 0);
                    if ($qty <= 0) continue;

                    if (isset($exItem['product_variant_id']) && $exItem['product_variant_id']) {
                        $lockedVariant = ProductVariant::where('id', $exItem['product_variant_id'])->lockForUpdate()->first();
                        if (!$lockedVariant) throw new Exception("Varian produk tukar tidak ditemukan.");
                        if ($lockedVariant->stock < $qty) throw new Exception("Stok produk tukar tidak mencukupi ({$lockedVariant->name}).");

                        $lockedProduct = Product::find($lockedVariant->product_id);
                        $price = $lockedVariant->pos_price ?: ($lockedVariant->price ?: $lockedProduct->price);
                        $finalPrice = $lockedVariant->pos_discount_price ?: $price;

                        $exchangedSubtotal += ($finalPrice * $qty);
                        $exchangedItemsData[] = [
                            'product_id'         => $lockedProduct->id,
                            'product_variant_id' => $lockedVariant->id,
                            'name'               => $lockedProduct->name . ' - ' . $lockedVariant->name,
                            'quantity'           => $qty,
                            'price'              => $finalPrice,
                            'total'              => $finalPrice * $qty,
                            'locked_model'       => $lockedVariant,
                        ];
                    } else {
                        $lockedProduct = Product::where('id', $exItem['product_id'])->lockForUpdate()->first();
                        if (!$lockedProduct) throw new Exception("Produk tukar tidak ditemukan.");
                        if ($lockedProduct->stock < $qty) throw new Exception("Stok produk tukar tidak mencukupi ({$lockedProduct->name}).");

                        $price = $lockedProduct->pos_price ?: $lockedProduct->price;
                        $finalPrice = $lockedProduct->pos_discount_price ?: $price;

                        $exchangedSubtotal += ($finalPrice * $qty);
                        $exchangedItemsData[] = [
                            'product_id'         => $lockedProduct->id,
                            'product_variant_id' => null,
                            'name'               => $lockedProduct->name,
                            'quantity'           => $qty,
                            'price'              => $finalPrice,
                            'total'              => $finalPrice * $qty,
                            'locked_model'       => $lockedProduct,
                        ];
                    }
                }
            }

            $netAmount = $exchangedSubtotal - $returnedSubtotal;

            // 3. Validasi Otorisasi Supervisor jika ada pengembalian uang dari laci (netAmount < 0 atau type == refund) melebihi limit tanpa PIN
            $maxWithoutPin = (int) (\App\Models\SiteSetting::where('key', 'pos_refund_max_without_pin')->value('value') ?? 0);
            $refundAmount  = abs($netAmount);

            $supervisor = null;
            if (($netAmount < 0 || $type === 'refund') && $refundAmount > $maxWithoutPin) {
                if (!$supervisorId || !$supervisorPin) {
                    throw new Exception("Pengembalian uang (Rp " . number_format($refundAmount, 0, ',', '.') . ") melebihi batas tanpa PIN (Rp " . number_format($maxWithoutPin, 0, ',', '.') . ") sehingga membutuhkan otorisasi PIN Supervisor.");
                }

                $supervisor = User::find($supervisorId);
                $isSupRole = $supervisor && (
                    $supervisor->is_pos_supervisor ||
                    in_array($supervisor->role, ['super_admin', 'owner', 'admin', 'manager', 'supervisor']) ||
                    $supervisor->hasAnyRole(['super_admin', 'owner', 'admin', 'manager', 'supervisor'])
                );
                $isValidPin = $supervisor && $supervisor->pos_pin && \Illuminate\Support\Facades\Hash::check($supervisorPin, $supervisor->pos_pin);

                if (!$supervisor || !$isSupRole || !$isValidPin) {
                    throw new Exception("PIN Supervisor tidak valid untuk pengembalian uang kas.");
                }
            }

            // 4. Buat Record PosReturn
            $posReturn = \App\Models\PosReturn::create([
                'return_number'         => 'RET-TEMP-' . uniqid(),
                'order_id'              => $order->id,
                'pos_session_id'        => $posSessionId,
                'cashier_id'            => $cashierId,
                'supervisor_id'         => $supervisor ? $supervisor->id : null,
                'type'                  => $type,
                'reason'                => $reason,
                'returned_subtotal'     => $returnedSubtotal,
                'exchanged_subtotal'    => $exchangedSubtotal,
                'net_amount'            => $netAmount,
                'refund_payment_method' => $netAmount < 0 ? $refundPaymentMethod : null,
                'refund_bank_name'      => $netAmount < 0 && $refundPaymentMethod === 'bank' ? $refundBankName : null,
                'refund_bank_account'   => $netAmount < 0 && $refundPaymentMethod === 'bank' ? $refundBankAccount : null,
            ]);

            $returnNumber = 'RET-' . date('Ymd') . '-' . str_pad($posReturn->id, 4, '0', STR_PAD_LEFT);
            $posReturn->update(['return_number' => $returnNumber]);

            // 5. Restock Barang Retur & Catat StockLog (IN)
            foreach ($returnedItemsData as $rItem) {
                if ($rItem['product_variant_id']) {
                    $var = ProductVariant::find($rItem['product_variant_id']);
                    if ($var) {
                        $before = $var->stock;
                        $var->increment('stock', $rItem['quantity']);
                        $after = $before + $rItem['quantity'];

                        StockLog::create([
                            'product_id'         => $rItem['product_id'],
                            'product_variant_id' => $rItem['product_variant_id'],
                            'user_id'            => $cashierId,
                            'type'               => 'in',
                            'quantity_before'    => $before,
                            'quantity_change'    => $rItem['quantity'],
                            'quantity_after'     => $after,
                            'reason'             => 'pos_return',
                            'notes'              => 'Restock Retur POS #' . $posReturn->return_number . ' (Nota #' . $order->order_number . ')',
                        ]);
                    }
                } else {
                    $prd = Product::find($rItem['product_id']);
                    if ($prd) {
                        $before = $prd->stock;
                        $prd->increment('stock', $rItem['quantity']);
                        $after = $before + $rItem['quantity'];

                        StockLog::create([
                            'product_id'         => $rItem['product_id'],
                            'product_variant_id' => null,
                            'user_id'            => $cashierId,
                            'type'               => 'in',
                            'quantity_before'    => $before,
                            'quantity_change'    => $rItem['quantity'],
                            'quantity_after'     => $after,
                            'reason'             => 'pos_return',
                            'notes'              => 'Restock Retur POS #' . $posReturn->return_number . ' (Nota #' . $order->order_number . ')',
                        ]);
                    }
                }

                \App\Models\PosReturnItem::create([
                    'pos_return_id'      => $posReturn->id,
                    'product_id'         => $rItem['product_id'],
                    'product_variant_id' => $rItem['product_variant_id'],
                    'type'               => 'returned',
                    'quantity'           => $rItem['quantity'],
                    'price'              => $rItem['price'],
                    'total'              => $rItem['total'],
                ]);
            }

            // 6. Deduct Barang Tukar & Catat StockLog (OUT)
            foreach ($exchangedItemsData as $eItem) {
                $lockedModel = $eItem['locked_model'];
                $before = $lockedModel->stock;
                $lockedModel->decrement('stock', $eItem['quantity']);
                $after = $before - $eItem['quantity'];

                StockLog::create([
                    'product_id'         => $eItem['product_id'],
                    'product_variant_id' => $eItem['product_variant_id'],
                    'user_id'            => $cashierId,
                    'type'               => 'out',
                    'quantity_before'    => $before,
                    'quantity_change'    => -$eItem['quantity'],
                    'quantity_after'     => $after,
                    'reason'             => 'pos_exchange',
                    'notes'              => 'Penukaran Barang POS #' . $posReturn->return_number . ' (Nota #' . $order->order_number . ')',
                ]);

                \App\Models\PosReturnItem::create([
                    'pos_return_id'      => $posReturn->id,
                    'product_id'         => $eItem['product_id'],
                    'product_variant_id' => $eItem['product_variant_id'],
                    'type'               => 'exchanged',
                    'quantity'           => $eItem['quantity'],
                    'price'              => $eItem['price'],
                    'total'              => $eItem['total'],
                ]);
            }

            // 7. Catat Cashflow
            if ($netAmount > 0) {
                Cashflow::create([
                    'transaction_date' => now()->toDateString(),
                    'type'             => 'in',
                    'category'         => 'pos_exchange_pay',
                    'amount'           => $netAmount,
                    'description'      => 'Selisih Tambah Penukaran Barang POS #' . $posReturn->return_number . ' (Nota #' . $order->order_number . ')',
                    'order_id'         => null,
                    'source'           => 'pos',
                    'is_reversed'      => false,
                ]);
            } elseif ($netAmount < 0) {
                $isBankRefund = ($refundPaymentMethod === 'bank');
                $bankDetailStr = ($isBankRefund && ($refundBankName || $refundBankAccount)) 
                    ? (' (' . trim($refundBankName . ' ' . $refundBankAccount) . ')') 
                    : '';
                Cashflow::create([
                    'transaction_date' => now()->toDateString(),
                    'type'             => 'out',
                    'category'         => $isBankRefund ? 'pos_return_refund_bank' : 'pos_return_refund',
                    'amount'           => abs($netAmount),
                    'description'      => 'Pengembalian Uang Retur POS #' . $posReturn->return_number . ' (' . ($isBankRefund ? 'Transfer Bank' : 'Tunai Kasir') . ')' . $bankDetailStr . ' (Disetujui Supervisor: ' . ($supervisor ? $supervisor->name : '-') . ')',
                    'order_id'         => $order->id,
                    'source'           => 'pos',
                    'is_reversed'      => false,
                ]);
            }

            return $posReturn;
        });
    }
}
