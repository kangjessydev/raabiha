<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Notifications\Notification;

class ProductVariantObserver
{
    public function updated(ProductVariant $variant): void
    {
        if ($variant->isDirty('stock')) {
            $newStock = $variant->stock;
            $oldStock = $variant->getOriginal('stock');

            // Ambil batas threshold
            $threshold = $this->getThreshold($variant);

            if ($newStock <= $threshold && $oldStock > $threshold) {
                $productName = $variant->product?->name ?? 'Produk';
                $fullName = "{$productName} ({$variant->name})";
                $this->notifyLowStock($fullName, $newStock);
            }
        }
    }

    protected function getThreshold(ProductVariant $variant): int
    {
        // 1. Cek stok minimum di level Varian
        if ($variant->minimum_stock !== null) {
            return (int) $variant->minimum_stock;
        }

        // 2. Cek stok minimum di level Produk induk
        if ($variant->product && $variant->product->minimum_stock !== null) {
            return (int) $variant->product->minimum_stock;
        }

        // 3. Cek stok minimum di level Global Setting
        $globalThreshold = SiteSetting::where('key', 'default_minimum_stock')->value('value');
        if (filled($globalThreshold)) {
            return (int) $globalThreshold;
        }

        // 4. Fallback default
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
                ->title('⚠️ Stok Varian Hampir Habis')
                ->body("Varian produk **{$name}** tersisa **{$stock}** unit saja.")
                ->sendToDatabase($admin);
        }
    }
}
