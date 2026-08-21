<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reason',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity_before'  => 'integer',
        'quantity_change'  => 'integer',
        'quantity_after'   => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getReasonOptions(): array
    {
        $defaultReasons = [
            'Restock'              => 'Restock / Barang Masuk Supplier',
            'Retur'                => 'Retur Pelanggan / Tukar Barang',
            'Koreksi'              => 'Koreksi Stok / Opname Physical',
            'Rusak'                => 'Barang Rusak / Kadaluwarsa',
            'Lainnya'              => 'Lainnya',
            'pos_sale'             => 'Penjualan POS Kasir',
            'pos_return'           => 'Retur POS Kasir',
            'pos_exchange'         => 'Tukar Barang POS Kasir',
            'pos_void'             => 'Pembatalan Nota POS (Void)',
            'checkout'             => 'Penjualan E-Commerce',
            'order_cancelled'      => 'Pembatalan E-Commerce',
        ];

        $customSetting = SiteSetting::where('key', 'stock_custom_reasons')->value('value');
        if ($customSetting) {
            $customArray = is_string($customSetting) ? json_decode($customSetting, true) : $customSetting;
            if (is_array($customArray)) {
                foreach ($customArray as $cReason) {
                    $rName = is_array($cReason) ? ($cReason['reason_name'] ?? null) : $cReason;
                    if (!empty($rName)) {
                        $defaultReasons[$rName] = $rName;
                    }
                }
            }
        }

        return $defaultReasons;
    }
}
