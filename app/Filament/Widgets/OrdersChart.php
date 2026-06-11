<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrdersChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Tren Pesanan Harian (Bulan Ini)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $daysInMonth = Carbon::now()->daysInMonth;
        $completedData = array_fill(0, $daysInMonth, 0);
        $cancelledData = array_fill(0, $daysInMonth, 0);
        $labels = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $labels[] = $i;
        }

        // We use string 'day' mapping to be generic since DAY() is MySQL specific, but we'll stick to it as requested in docs
        $completedOrders = Order::selectRaw('DAY(created_at) as day, COUNT(*) as count')
            ->whereMonth('created_at', now()->month)
            ->where('status', 'completed')
            ->groupBy('day')
            ->get();

        foreach ($completedOrders as $order) {
            $completedData[$order->day - 1] = $order->count;
        }

        $cancelledOrders = Order::selectRaw('DAY(created_at) as day, COUNT(*) as count')
            ->whereMonth('created_at', now()->month)
            ->where('status', 'cancelled')
            ->groupBy('day')
            ->get();

        foreach ($cancelledOrders as $order) {
            $cancelledData[$order->day - 1] = $order->count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pesanan Selesai',
                    'data' => $completedData,
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Pesanan Batal',
                    'data' => $cancelledData,
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
