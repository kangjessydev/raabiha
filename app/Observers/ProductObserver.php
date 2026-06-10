<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Notifications\Notification;

class ProductObserver
{
    public function updated(Product $product): void
    {
        // Hanya cek jika tidak memiliki varian (karena jika punya varian, stok dihitung dari variannya)
        if (!$product->has_variants && $product->isDirty('stock')) {
            $newStock = $product->stock;
            $oldStock = $product->getOriginal('stock');

            // Ambil batas threshold
            $threshold = $this->getThreshold($product);

            if ($newStock <= $threshold && $oldStock > $threshold) {
                $this->notifyLowStock($product->name, $newStock);
            }
        }
    }

    protected function getThreshold(Product $product): int
    {
        // 1. Cek stok minimum di level produk
        if ($product->minimum_stock !== null) {
            return (int) $product->minimum_stock;
        }

        // 2. Cek stok minimum di level Global Setting
        $globalThreshold = SiteSetting::where('key', 'default_minimum_stock')->value('value');
        if (filled($globalThreshold)) {
            return (int) $globalThreshold;
        }

        // 3. Fallback default
        return 5;
    }

    protected function notifyLowStock(string $name, int $stock): void
    {
        $admins = User::role('super_admin')->get();
        if ($admins->isEmpty()) {
            $admins = User::where('id', 2)->get();
        }

        foreach ($admins as $admin) {
            Notification::make()
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('danger')
                ->title('⚠️ Stok Hampir Habis')
                ->body("Produk **{$name}** tersisa **{$stock}** unit saja.")
                ->sendToDatabase($admin);
        }
    }
}
