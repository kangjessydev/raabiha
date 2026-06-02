<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Tren Pesanan Harian';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Pesanan Selesai',
                    'data' => [12, 19, 15, 25, 22, 30, 28],
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Pesanan Batal',
                    'data' => [2, 3, 1, 4, 2, 1, 3],
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
