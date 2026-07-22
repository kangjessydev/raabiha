<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLog;
use App\Models\Cashflow;
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

            // Generate Order Number
            $latestOrder = Order::orderBy('id', 'desc')->first();
            $nextId = $latestOrder ? $latestOrder->id + 1 : 1;
            $orderNumber = 'POS-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // 2. Buat Order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => null, // Pembeli di toko fisik
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'cashier_id' => $cashierId,
                'pos_session_id' => $posSessionId,
                'source' => 'pos', // Penting untuk guard observer
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
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
                    'notes' => 'Penjualan POS #' . $orderNumber,
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
                'category' => 'Sales',
                'amount' => $grandTotal,
                'description' => 'Penjualan POS #' . $orderNumber,
                'order_id' => $order->id,
                'source' => 'order',
                'is_reversed' => false,
            ]);

            return $order;
        });
    }
}
