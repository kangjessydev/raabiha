<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;

class LaporanStokOverviewWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $products = Product::all();
        
        $totalHppValuation = $products->sum(fn ($p) => ($p->purchase_price ?? 0) * $p->stock);
        $totalRetailValuation = $products->sum(fn ($p) => ($p->price ?? 0) * $p->stock);
        $lowStockCount = $products->filter(fn ($p) => $p->stock <= 5 && $p->stock > 0)->count();
        $outOfStockCount = $products->filter(fn ($p) => $p->stock <= 0)->count();

        $formatRupiah = fn($val) => 'Rp ' . number_format($val, 0, ',', '.');

        return [
            Stat::make('Total Valuasi HPP Modal', $formatRupiah($totalHppValuation))
                ->description('Total Nilai Aset Gudang Fisik')
                ->descriptionIcon('heroicon-o-cube')
                ->color('success'),

            Stat::make('Proyeksi Nilai Retail', $formatRupiah($totalRetailValuation))
                ->description('Nilai Penjualan Retail')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('info'),

            Stat::make('Stok Kritis (≤ 5 Unit)', number_format($lowStockCount) . ' SKU')
                ->description('Perlu Restock Segera')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Stok Habis (0 Unit)', number_format($outOfStockCount) . ' SKU')
                ->description('Habis Terjual')
                ->descriptionIcon('heroicon-o-archive-box-x-mark')
                ->color('danger'),
        ];
    }
}
