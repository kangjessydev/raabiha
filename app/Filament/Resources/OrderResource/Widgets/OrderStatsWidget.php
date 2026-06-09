<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsWidget extends BaseWidget
{
    public string $period = 'today';

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.order-stats-widget';

    protected function getPeriodDates(): array
    {
        return match ($this->period) {
            'today'  => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'week'   => [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()],
            'month'  => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year'   => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default  => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };
    }

    public function getPeriodLabel(): string
    {
        return match ($this->period) {
            'today'  => 'Hari Ini',
            'week'   => '7 Hari Terakhir',
            'month'  => 'Bulan Ini',
            'year'   => 'Tahun Ini',
            default  => 'Hari Ini',
        };
    }

    public function updatedPeriod(): void
    {
        $this->cachedStats = null;
    }

    public function getPeriods(): array
    {
        return [
            'today' => 'Hari Ini',
            'week'  => '7 Hari Terakhir',
            'month' => 'Bulan Ini',
            'year'  => 'Tahun Ini',
        ];
    }

    protected function getStats(): array
    {
        [$start, $end] = $this->getPeriodDates();
        $label = $this->getPeriodLabel();

        $totalOrders = Order::whereBetween('created_at', [$start, $end])->count();

        $revenue = Order::whereBetween('created_at', [$start, $end])
            ->whereIn('payment_status', ['paid', 'PAID'])
            ->sum('grand_total');

        $pendingOrders = Order::whereIn('status', ['pending', 'PENDING'])->count();

        return [
            Stat::make("Pesanan Baru ({$label})", $totalOrders)
                ->description('Total transaksi masuk ' . strtolower($label))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([2, 5, 3, 7, 4, 10, max($totalOrders, 1)])
                ->color('info')
                ->icon('heroicon-o-shopping-cart')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-blue-50/50 to-blue-100/50 rounded-xl cursor-pointer',
                ]),
            Stat::make("Pendapatan ({$label})", 'Rp ' . number_format($revenue, 0, ',', '.'))
                ->description('Dari pesanan berstatus Lunas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([100000, 250000, 150000, 400000, 200000, 500000, max($revenue, 1)])
                ->color('success')
                ->icon('heroicon-o-currency-dollar')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-emerald-50/50 to-emerald-100/50 rounded-xl cursor-pointer',
                ]),
            Stat::make('Menunggu Pembayaran', $pendingOrders)
                ->description('Pesanan berstatus Pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-amber-50/50 to-amber-100/50 rounded-xl cursor-pointer',
                ]),
        ];
    }
}
