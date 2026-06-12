<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class LaporanBisnisDoughnutChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Distribusi Status Pesanan';

    protected ?string $maxHeight = '320px';

    public ?array $filters = null;

    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    public function updateFilters($filters)
    {
        $this->filters = $filters;
        $this->dispatch('$refresh');
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $from = $this->filters['created_from'] ?? now()->subDays(29)->toDateString();
        $until = $this->filters['created_until'] ?? now()->toDateString();

        $statuses = [
            'completed' => 'Selesai',
            'pending' => 'Menunggu Bayar',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'cancelled' => 'Batal',
        ];

        $data = [];
        $labels = [];
        $colors = [
            '#10b981', // green (completed)
            '#f59e0b', // amber (pending)
            '#0ea5e9', // light blue (processing)
            '#8b5cf6', // purple (shipped)
            '#ef4444', // red (cancelled)
        ];

        foreach ($statuses as $key => $label) {
            $count = Order::where('status', $key)
                ->whereBetween('created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])
                ->count();
                
            $data[] = $count;
            $labels[] = $label;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pesanan',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '65%',
        ];
    }
}
