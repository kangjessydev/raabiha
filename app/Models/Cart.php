<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'is_buy_now',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Pindahkan / gabungkan keranjang guest ke akun user setelah Login atau Register (K-03).
     */
    public static function mergeGuestCartToUser(string $oldSessionId, int $userId): void
    {
        if ($oldSessionId === '') {
            return;
        }

        $guestCarts = static::with(['items.product', 'items.variant'])
            ->where('session_id', $oldSessionId)
            ->whereNull('user_id')
            ->get();

        foreach ($guestCarts as $guestCart) {
            if ($guestCart->items->isEmpty()) {
                $guestCart->delete();
                continue;
            }

            $userCart = static::firstOrCreate([
                'user_id' => $userId,
                'is_buy_now' => (bool) $guestCart->is_buy_now,
            ]);

            foreach ($guestCart->items as $guestItem) {
                $existing = $userCart->items()
                    ->where('product_id', $guestItem->product_id)
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();

                $availableStock = $guestItem->product_variant_id !== null
                    ? (int) ($guestItem->variant?->stock ?? 0)
                    : (int) ($guestItem->product?->stock ?? 0);

                if ($existing) {
                    $mergedQty = $existing->quantity + $guestItem->quantity;
                    if ($availableStock > 0) {
                        $mergedQty = min($mergedQty, $availableStock);
                    }
                    $existing->update(['quantity' => max(1, $mergedQty)]);
                    $guestItem->delete();
                } else {
                    $qty = $availableStock > 0 ? min($guestItem->quantity, $availableStock) : $guestItem->quantity;
                    $guestItem->update([
                        'cart_id' => $userCart->id,
                        'quantity' => max(1, $qty),
                    ]);
                }
            }

            $guestCart->delete();
        }
    }
}
