<?php

namespace App\Filament\Widgets;

use App\Models\Cashflow;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SalesTrendChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Tren Penjualan vs Pengeluaran (30 Hari Terakhir)';

    protected string $color = 'success';

    protected ?string $maxHeight = '280px';

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '120s';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $cacheKey = 'sales_trend_chart_' . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 300, function () {
            $days = collect(range(29, 0))->map(fn ($i) => Carbon::today()->subDays($i)->toDateString());

            // Ambil semua data penjualan 30 hari sekaligus — 1 query
            $sales = Cashflow::select(DB::raw('DATE(transaction_date) as date'), DB::raw('SUM(amount) as total'))
                ->where('type', 'in')
                ->where('source', 'order')
                ->where('is_reversed', false)
                ->whereBetween('transaction_date', [$days->first(), $days->last()])
                ->groupBy('date')
                ->pluck('total', 'date');

            // Ambil semua data pengeluaran 30 hari sekaligus — 1 query
            $expenses = Cashflow::select(DB::raw('DATE(transaction_date) as date'), DB::raw('SUM(amount) as total'))
                ->where('type', 'out')
                ->where('source', 'manual')
                ->whereBetween('transaction_date', [$days->first(), $days->last()])
                ->groupBy('date')
                ->pluck('total', 'date');

            return [
                'datasets' => [
                    [
                        'label'                => 'Penjualan',
                        'data'                 => $days->map(fn ($d) => (int) ($sales[$d] ?? 0))->values()->toArray(),
                        'borderColor'          => '#10b981',
                        'backgroundColor'      => 'rgba(16, 185, 129, 0.08)',
                        'fill'                 => true,
                        'tension'              => 0.4,
                        'pointRadius'          => 3,
                    ],
                    [
                        'label'                => 'Pengeluaran',
                        'data'                 => $days->map(fn ($d) => (int) ($expenses[$d] ?? 0))->values()->toArray(),
                        'borderColor'          => '#ef4444',
                        'backgroundColor'      => 'rgba(239, 68, 68, 0.06)',
                        'fill'                 => true,
                        'tension'              => 0.4,
                        'pointRadius'          => 3,
                    ],
                ],
                'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('d M'))->values()->toArray(),
            ];
        });
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'top'],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": Rp " + context.parsed.y.toLocaleString("id-ID");
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
        ];
    }
}
