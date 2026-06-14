<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'name',
        'code',
        'discount_type',
        'discount_amount',
        'is_shipping_voucher',
        'min_purchase',
        'min_items',
        'max_discount',
        'specific_users',
        'exclude_resellers',
        'is_stackable',
        'free_gift_product_id',
        'max_uses',
        'max_uses_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'specific_users' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_shipping_voucher' => 'boolean',
        'exclude_resellers' => 'boolean',
        'is_stackable' => 'boolean',
        'discount_amount' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'max_uses_per_user' => 'integer',
    ];

    public function freeGiftProduct()
    {
        return $this->belongsTo(Product::class, 'free_gift_product_id');
    }

    protected static function booted()
    {
        static::saved(function ($voucher) {
            cache()->forget('global_promo_labels');
        });

        static::deleted(function ($voucher) {
            cache()->forget('global_promo_labels');
        });
    }

    public static function getGlobalPromoLabels(): array
    {
        return cache()->remember('global_promo_labels', 300, function () {
            $vouchers = self::where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->where(function($q) {
                    $q->whereNull('specific_users')->orWhere('specific_users', '[]')->orWhere('specific_users', '');
                })
                ->get();

            $labels = [];

            foreach ($vouchers as $voucher) {
                if ($voucher->is_shipping_voucher) {
                    $labels[] = [
                        'type' => 'shipping',
                        'text' => 'Gratis Ongkir',
                        'color' => 'text-[#064e3b]',
                        'bg' => 'bg-[#e6f4ea]',
                        'border' => 'border-[#064e3b]/20',
                        'icon' => '<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>'
                    ];
                } else {
                    $text = $voucher->discount_type === 'percent' 
                        ? 'Diskon ' . round($voucher->discount_amount) . '%' 
                        : 'Voucher Rp' . number_format($voucher->discount_amount, 0, ',', '.');
                    
                    $labels[] = [
                        'type' => 'discount',
                        'text' => $text,
                        'color' => 'text-[#b91c1c]',
                        'bg' => 'bg-white',
                        'border' => 'border-[#b91c1c]/30',
                        'icon' => '' // Can add SVG if needed
                    ];
                }
            }

            // Return unique labels by text to avoid duplicates
            return collect($labels)->unique('text')->values()->toArray();
        });
    }
}
