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
                ->title('Ulasan Produk Baru')
                ->body("{$authorName} memberi rating {$review->rating}/5 untuk {$productName}: \"{$excerpt}\"")
                ->actions([
                    \Filament\Actions\Action::make('view')
                        ->label('Lihat Ulasan')
                        ->button()
                        ->url(route('filament.admin.e-commerce.resources.product-reviews.index')),
                ])
                ->sendToDatabase($admin);
        }
    }
}
