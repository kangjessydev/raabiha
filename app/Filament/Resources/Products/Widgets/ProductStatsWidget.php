<?php

namespace App\Filament\Resources\Products\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        
        // Simple stock calculation
        $outOfStock = Product::where(function($query) {
            $query->where('has_variants', false)->where('stock', '<=', 0);
        })->orWhere(function($query) {
            $query->where('has_variants', true)->whereHas('variants', function($q) {
                // Not perfectly accurate in a single query without complex joins, 
                // but good enough for a dashboard flag
                $q->where('stock', '<=', 0);
            });
        })->count();

        return [
            Stat::make('Total Produk', $totalProducts)
                ->description('Seluruh produk dalam katalog')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info')
                ->icon('heroicon-o-archive-box')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-blue-50/50 to-blue-100/50 rounded-xl cursor-pointer',
                ]),
            Stat::make('Produk Aktif', $activeProducts)
                ->description('Produk yang terlihat oleh pelanggan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-emerald-50/50 to-emerald-100/50 rounded-xl cursor-pointer',
                ]),
            Stat::make('Stok Habis / Menipis', $outOfStock)
                ->description('Produk/Varian yang perlu restock')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->icon('heroicon-o-exclamation-circle')
                ->url(\App\Filament\Resources\StockManagement\StockManagementResource::getUrl('index'))
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-rose-50/50 to-rose-100/50 rounded-xl cursor-pointer',
                ]),
        ];
    }
}
