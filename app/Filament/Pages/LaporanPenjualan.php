<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\SalesTrendChart;
use App\Filament\Widgets\OrdersChart;
use App\Filament\Widgets\RevenueChart;

class LaporanPenjualan extends Page
{
    // Mengamankan halaman ini dengan permission standard Filament Shield
    // Hanya user yang diizinkan (super_admin, dsb) yang bisa melihat halaman ini
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-pie';

    protected static \UnitEnum|string|null $navigationGroup = 'Transaksi';

    protected static ?string $title = 'Laporan & Statistik';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.laporan-penjualan'; // Kita buat blade sederhana

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1; // Menampilkan widget secara vertical penuh (full width)
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesTrendChart::class,
            RevenueChart::class,
            OrdersChart::class,
        ];
    }
}
