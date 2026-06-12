<?php

namespace App\Filament\Widgets;

use App\Models\Cashflow;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class LaporanBisnisChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Grafik Tren Penjualan vs Pengeluaran';

    protected string $color = 'success';

    protected ?string $maxHeight = '320px';

    protected int | string | array $columnSpan = 'full';

    public ?array $filters = null;

    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    public function updateFilters($filters)
    {
        $this->filters = $filters;
        $this->dispatch('$refresh');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $from = $this->filters['created_from'] ?? now()->subDays(29)->toDateString();
        $until = $this->filters['created_until'] ?? now()->toDateString();

        $startDate = Carbon::parse($from);
        $endDate = Carbon::parse($until);
        $diffInDays = $startDate->diffInDays($endDate);

        $labels = [];
        $revenueData = [];
        $expenseData = [];

        if ($diffInDays > 90) {
            // Group by Month (wide range)
            $periods = Carbon::parse($from)->startOfMonth()->period(Carbon::parse($until)->endOfMonth(), '1 month');
            foreach ($periods as $period) {
                $monthStr = $period->format('Y-m');
                $labels[] = $period->format('M Y');
                
                $revenueData[] = (int) Cashflow::where('type', 'in')
                    ->where('is_reversed', false)
                    ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$monthStr])
                    ->sum('amount');
                    
                $expenseData[] = (int) Cashflow::where('type', 'out')
                    ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$monthStr])
                    ->sum('amount');
            }
        } else {
            // Group by Day (standard range)
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->toDateString();
                $labels[] = $date->format('d M');
                
                $revenueData[] = (int) Cashflow::where('type', 'in')
                    ->where('is_reversed', false)
                    ->where('transaction_date', $dateStr)
                    ->sum('amount');
                    
                $expenseData[] = (int) Cashflow::where('type', 'out')
                    ->where('transaction_date', $dateStr)
                    ->sum('amount');
            }
        }

        return [
            'datasets' => [
                [
                    'label'                => 'Penjualan (Uang Masuk)',
                    'data'                 => $revenueData,
                    'borderColor'          => '#10b981',
                    'backgroundColor'      => 'rgba(16, 185, 129, 0.08)',
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointRadius'          => 3,
                ],
                [
                    'label'                => 'Pengeluaran (Kas Keluar)',
                    'data'                 => $expenseData,
                    'borderColor'          => '#ef4444',
                    'backgroundColor'      => 'rgba(239, 68, 68, 0.06)',
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointRadius'          => 3,
                ],
            ],
            'labels' => $labels,
        ];
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
