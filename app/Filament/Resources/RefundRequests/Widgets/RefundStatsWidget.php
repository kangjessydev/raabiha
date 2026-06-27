<?php

namespace App\Filament\Resources\RefundRequests\Widgets;

use App\Models\RefundRequest;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RefundStatsWidget extends BaseWidget
{
    public string $period = 'today';

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.refund-stats-widget';

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

        $totalRequests = RefundRequest::whereBetween('created_at', [$start, $end])->count();

        $totalRefunded = RefundRequest::whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['completed', 'approved'])
            ->sum('refund_amount');

        $pendingRequests = RefundRequest::where('status', 'pending')->count();

        return [
            Stat::make("Pengajuan Baru ({$label})", $totalRequests)
                ->description('Total pengajuan refund ' . strtolower($label))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([2, 5, 3, 7, 4, 10, max($totalRequests, 1)])
                ->color('info')
                ->icon('heroicon-o-document-text')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-blue-50/50 to-blue-100/50 rounded-xl cursor-pointer',
                ]),
            Stat::make("Total Refund ({$label})", 'Rp ' . number_format($totalRefunded, 0, ',', '.'))
                ->description('Dari refund berstatus Disetujui/Selesai')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart([200000, 120000, 150000, 75000, 100000, 50000, max($totalRefunded, 1)])
                ->color('danger')
                ->icon('heroicon-o-currency-dollar')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-rose-50/50 to-rose-100/50 rounded-xl cursor-pointer',
                ]),
            Stat::make('Menunggu Diproses', $pendingRequests)
                ->description('Pengajuan berstatus Pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-amber-50/50 to-amber-100/50 rounded-xl cursor-pointer',
                ]),
        ];
    }
}
