<?php

namespace App\Observers;

use App\Models\ProductReview;
use App\Models\User;
use Filament\Notifications\Notification;

class ProductReviewObserver
{
    public function created(ProductReview $review): void
    {
        $product = $review->product;
        $authorName = $review->user?->name ?? $review->customer_name ?? 'Pelanggan';
        $ratingStars = str_repeat('⭐', $review->rating);
        $excerpt = \Str::limit($review->comment, 80);
        $productName = $product?->name ?? 'Produk';

        $admins = User::role('super_admin')->get();
        if ($admins->isEmpty()) {
            $admins = User::where('id', 2)->get();
        }

        foreach ($admins as $admin) {
            Notification::make()
                ->icon('heroicon-o-star')
                ->iconColor('warning')
                ->title('⭐ Ulasan Produk Baru')
                ->body("**{$authorName}** memberi rating {$ratingStars} untuk **{$productName}**: \"{$excerpt}\"")
                ->sendToDatabase($admin);
        }
    }
}
