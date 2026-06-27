<?php

namespace App\Filament\Resources\Vouchers\Widgets;

use App\Models\Voucher;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VoucherStatsWidget extends BaseWidget
{
    public string $period = 'today';

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.voucher-stats-widget';

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

        $vouchersCreated = Voucher::whereBetween('created_at', [$start, $end])->count();

        $vouchersUsed = Order::whereBetween('created_at', [$start, $end])
            ->whereNotNull('voucher_id')
            ->whereIn('payment_status', ['paid', 'PAID'])
            ->count();

        $discountGiven = Order::whereBetween('created_at', [$start, $end])
            ->whereNotNull('voucher_id')
            ->whereIn('payment_status', ['paid', 'PAID'])
            ->sum('discount_total');

        return [
            Stat::make("Voucher Dibuat ({$label})", $vouchersCreated)
                ->description('Total kampanye / voucher baru')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info')
                ->icon('heroicon-o-tag')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-blue-50/50 to-blue-100/50 rounded-xl cursor-pointer',
                ]),
            Stat::make("Voucher Digunakan ({$label})", $vouchersUsed)
                ->description('Pesanan yang menggunakan voucher')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([2, 5, 3, 7, 4, 10, max($vouchersUsed, 1)])
                ->color('success')
                ->icon('heroicon-o-shopping-bag')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-emerald-50/50 to-emerald-100/50 rounded-xl cursor-pointer',
                ]),
            Stat::make("Total Diskon Diberikan ({$label})", 'Rp ' . number_format($discountGiven, 0, ',', '.'))
                ->description('Potongan harga (Pesanan Lunas)')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart([100000, 75000, 150000, 120000, 200000, 50000, max($discountGiven, 1)])
                ->color('danger')
                ->icon('heroicon-o-currency-dollar')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-rose-50/50 to-rose-100/50 rounded-xl cursor-pointer',
                ]),
        ];
    }
}
