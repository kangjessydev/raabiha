<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Grafik Omset Tahun Ini';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $monthlyRevenue = array_fill(0, 12, 0);

        $revenues = Order::selectRaw('MONTH(created_at) as month, SUM(grand_total) as total')
            ->whereYear('created_at', now()->year)
            ->where('payment_status', 'paid')
            ->groupBy('month')
            ->get();

        foreach ($revenues as $revenue) {
            $monthlyRevenue[$revenue->month - 1] = $revenue->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Omset (Rp)',
                    'data' => $monthlyRevenue,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
