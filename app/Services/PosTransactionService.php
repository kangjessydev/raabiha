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

            // 2. Buat Order (Gunakan placeholder sementara untuk menjamin keunikan ID)
            $order = Order::create([
                'order_number' => 'POS-TEMP-' . uniqid(),
                'user_id' => null, // Pembeli di toko fisik
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'cashier_id' => $cashierId,
                'pos_session_id' => $posSessionId,
                'source' => 'pos', // Penting untuk guard observer
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'grand_total' => $grandTotal,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'cash_paid' => $cashPaid,
                'cash_change' => $cashChange,
                'payment_details' => is_array($paymentDetails) ? json_encode($paymentDetails) : $paymentDetails,
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

            return $order;
        });
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

            // 3. Validasi Otorisasi Supervisor jika ada pengembalian uang dari laci (netAmount < 0 atau type == refund)
            $supervisor = null;
            if ($netAmount < 0 || $type === 'refund') {
                if (!$supervisorId || !$supervisorPin) {
                    throw new Exception("Pengembalian uang kas memerlukan otorisasi PIN Supervisor.");
                }

                $supervisor = User::find($supervisorId);
                $isSupRole = $supervisor && ($supervisor->hasAnyRole(['super_admin', 'owner', 'manager', 'finance']) || in_array($supervisor->role, ['super_admin', 'owner', 'manager', 'finance']));
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
                'refund_payment_method' => $netAmount < 0 ? 'cash' : null,
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
                        $after = $var->stock;

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
                        $after = $prd->stock;

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
                Cashflow::create([
                    'transaction_date' => now()->toDateString(),
                    'type'             => 'out',
                    'category'         => 'pos_return_refund',
                    'amount'           => abs($netAmount),
                    'description'      => 'Pengembalian Uang Retur POS #' . $posReturn->return_number . ' (Disetujui Supervisor: ' . ($supervisor ? $supervisor->name : '-') . ')',
                    'order_id'         => $order->id,
                    'source'           => 'pos',
                    'is_reversed'      => false,
                ]);
            }

            return $posReturn;
        });
    }
}
