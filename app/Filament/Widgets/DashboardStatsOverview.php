<?php

namespace App\Filament\Widgets;

use App\Models\Cashflow;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardStatsOverview extends StatsOverviewWidget
{
    // Polling setiap 60 detik agar ringan di VPS 2GB
    protected ?string $pollingInterval = '60s';

    public string $period = 'today';

    protected string $view = 'filament.widgets.dashboard-stats-widget';

    protected function getPeriodDates(): array
    {
        return match ($this->period) {
            'today'  => [\Carbon\Carbon::today()->startOfDay(), \Carbon\Carbon::today()->endOfDay()],
            'week'   => [\Carbon\Carbon::now()->subDays(6)->startOfDay(), \Carbon\Carbon::now()->endOfDay()],
            'month'  => [\Carbon\Carbon::now()->startOfMonth(), \Carbon\Carbon::now()->endOfMonth()],
            'year'   => [\Carbon\Carbon::now()->startOfYear(), \Carbon\Carbon::now()->endOfYear()],
            default  => [\Carbon\Carbon::today()->startOfDay(), \Carbon\Carbon::today()->endOfDay()],
        };
    }

    public function updatedPeriod(): void
    {
        $this->cachedStats = null;
    }

    protected function getStats(): array
    {
        [$start, $end] = $this->getPeriodDates();
        $cacheKey = 'dashboard_overview_' . $this->period . '_' . now()->format('Y-m-d_H');

        // Cache 10 menit untuk periode panjang, 10 detik untuk today
        $cacheTime = $this->period === 'today' ? 10 : 600;

        $data = Cache::remember($cacheKey, $cacheTime, function () use ($start, $end) {
            // 1. Pesanan Menunggu Pembayaran
            $pendingPayment = Order::selectRaw('COUNT(*) as count, SUM(grand_total) as amount')
                ->whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'pending')
                ->first();

            // 2. Pesanan Selesai
            $completed = Order::selectRaw('COUNT(*) as count, SUM(grand_total) as amount')
                ->whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->first();

            // 3. Pesanan Batal
            $cancelled = Order::selectRaw('COUNT(*) as count, SUM(grand_total) as amount')
                ->whereBetween('created_at', [$start, $end])
                ->where('status', 'cancelled')
                ->first();

            // 4. Pengeluaran (Kas Keluar)
            $expense = Cashflow::whereBetween('transaction_date', [$start, $end])
                ->where('type', 'out')
                ->sum('amount');

            // 5. Voucher digunakan
            $vouchers = Order::selectRaw('COUNT(*) as count, SUM(discount_total) as discount')
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('voucher_id')
                ->first();

            // 6. Laba Bersih
            $revenue = Cashflow::whereBetween('transaction_date', [$start, $end])
                ->where('type', 'in')
                ->where('is_reversed', false)
                ->sum('amount');

            // 6b. HPP
            $hpp = (float) Order::where('orders.payment_status', 'paid')
                ->whereBetween('orders.created_at', [$start, $end])
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->leftJoin('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
                ->sum(DB::raw('COALESCE(order_items.purchase_price, product_variants.purchase_price, products.purchase_price, 0) * order_items.quantity'));

            // --- Query Tren untuk Sparkline ---
            // Grouping berdasarkan Hari atau Bulan tergantung periode
            $dateFormat = $this->period === 'today' ? 'HOUR(created_at)' : 'DATE(created_at)';
            $cashflowFormat = $this->period === 'today' ? 'HOUR(transaction_date)' : 'DATE(transaction_date)';

            $pendingHourly = Order::selectRaw("{$dateFormat} as dt, COUNT(*) as count")
                ->whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'pending')
                ->groupBy('dt')
                ->pluck('count', 'dt')
                ->toArray();

            $completedHourly = Order::selectRaw("{$dateFormat} as dt, COUNT(*) as count")
                ->whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->groupBy('dt')
                ->pluck('count', 'dt')
                ->toArray();

            $cancelledHourly = Order::selectRaw("{$dateFormat} as dt, COUNT(*) as count")
                ->whereBetween('created_at', [$start, $end])
                ->where('status', 'cancelled')
                ->groupBy('dt')
                ->pluck('count', 'dt')
                ->toArray();

            $hourlyExpenses = Cashflow::selectRaw("{$cashflowFormat} as dt, SUM(amount) as amount")
                ->whereBetween('transaction_date', [$start, $end])
                ->where('type', 'out')
                ->groupBy('dt')
                ->pluck('amount', 'dt')
                ->toArray();

            $voucherHourly = Order::selectRaw("{$dateFormat} as dt, COUNT(*) as count")
                ->whereBetween('created_at', [$start, $end])
                ->whereNotNull('voucher_id')
                ->groupBy('dt')
                ->pluck('count', 'dt')
                ->toArray();

            $hourlyRevenue = Cashflow::selectRaw("{$cashflowFormat} as dt, SUM(amount) as amount")
                ->whereBetween('transaction_date', [$start, $end])
                ->where('type', 'in')
                ->where('is_reversed', false)
                ->groupBy('dt')
                ->pluck('amount', 'dt')
                ->toArray();

            $pendingTrend = [];
            $completedTrend = [];
            $cancelledTrend = [];
            $expenseTrend = [];
            $voucherTrend = [];
            $profitTrend = [];

            if ($this->period === 'today') {
                $currentHour = (int) now()->format('H');
                for ($i = 5; $i >= 0; $i--) {
                    $h = ($currentHour - $i + 24) % 24;
                    $pendingTrend[] = (int) ($pendingHourly[$h] ?? 0);
                    $completedTrend[] = (int) ($completedHourly[$h] ?? 0);
                    $cancelledTrend[] = (int) ($cancelledHourly[$h] ?? 0);
                    $expenseTrend[] = (int) ($hourlyExpenses[$h] ?? 0);
                    $voucherTrend[] = (int) ($voucherHourly[$h] ?? 0);
                    $profitTrend[] = (int) (($hourlyRevenue[$h] ?? 0) - ($hourlyExpenses[$h] ?? 0));
                }
            } else {
                // Untuk selain today, kumpulkan dari array values
                $pendingTrend = array_values($pendingHourly) ?: [0];
                $completedTrend = array_values($completedHourly) ?: [0];
                $cancelledTrend = array_values($cancelledHourly) ?: [0];
                $expenseTrend = array_values($hourlyExpenses) ?: [0];
                $voucherTrend = array_values($voucherHourly) ?: [0];
                
                $allDates = array_unique(array_merge(array_keys($hourlyRevenue), array_keys($hourlyExpenses)));
                sort($allDates);
                foreach ($allDates as $d) {
                    $profitTrend[] = (int) (($hourlyRevenue[$d] ?? 0) - ($hourlyExpenses[$d] ?? 0));
                }
                if (empty($profitTrend)) $profitTrend = [0];
            }

            return [
                'pending_payment_count' => (int) ($pendingPayment->count ?? 0),
                'pending_payment_amount'=> (int) ($pendingPayment->amount ?? 0),
                'completed_count'       => (int) ($completed->count ?? 0),
                'completed_amount'      => (int) ($completed->amount ?? 0),
                'cancelled_count'       => (int) ($cancelled->count ?? 0),
                'cancelled_amount'      => (int) ($cancelled->amount ?? 0),
                'expense'               => (int) $expense,
                'vouchers_count'        => (int) ($vouchers->count ?? 0),
                'vouchers_discount'     => (int) ($vouchers->discount ?? 0),
                'revenue'               => (int) $revenue,
                'hpp'                   => (int) $hpp,
                
                // trends
                'pending_trend'         => $pendingTrend,
                'completed_trend'       => $completedTrend,
                'cancelled_trend'       => $cancelledTrend,
                'expense_trend'         => $expenseTrend,
                'voucher_trend'         => $voucherTrend,
                'profit_trend'          => $profitTrend,
            ];
        });

        $fmt = fn (int $value): string => 'Rp ' . number_format($value, 0, ',', '.');
        $netProfit = $data['revenue'] - $data['hpp'] - $data['expense'];

        return [
            // 1. Pesanan Menunggu Pembayaran
            Stat::make('Pesanan Menunggu', $data['pending_payment_count'] . ' Pesanan')
                ->description('Nominal: ' . $fmt($data['pending_payment_amount']))
                ->descriptionIcon('heroicon-m-clock')
                ->chart($data['pending_trend'])
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-amber-50/50 to-amber-100/50 rounded-xl cursor-pointer',
                ]),

            // 2. Pesanan Selesai
            Stat::make('Pesanan Selesai', $data['completed_count'] . ' Pesanan')
                ->description('Nominal: ' . $fmt($data['completed_amount']))
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart($data['completed_trend'])
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-emerald-50/50 to-emerald-100/50 rounded-xl cursor-pointer',
                ]),

            // 3. Pesanan Batal
            Stat::make('Pesanan Batal', $data['cancelled_count'] . ' Pesanan')
                ->description('Nominal: ' . $fmt($data['cancelled_amount']))
                ->descriptionIcon('heroicon-m-x-circle')
                ->chart($data['cancelled_trend'])
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-rose-50/50 to-rose-100/50 rounded-xl cursor-pointer',
                ]),

            // 4. Pengeluaran Hari Ini
            Stat::make('Pengeluaran', $fmt($data['expense']))
                ->description('Dari buku kas keluar')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart($data['expense_trend'])
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-red-50/50 to-red-100/50 rounded-xl cursor-pointer',
                ]),

            // 5. Voucher Digunakan
            Stat::make('Voucher Digunakan', $data['vouchers_count'] . ' Voucher')
                ->description('Total diskon: ' . $fmt($data['vouchers_discount']))
                ->descriptionIcon('heroicon-m-ticket')
                ->chart($data['voucher_trend'])
                ->color('info')
                ->icon('heroicon-o-ticket')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-blue-50/50 to-blue-100/50 rounded-xl cursor-pointer',
                ]),

            // 6. Laba Bersih
            Stat::make('Laba Bersih', $fmt($netProfit))
                ->description('Omset: ' . $fmt($data['revenue']) . ' | HPP: ' . $fmt($data['hpp']))
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($data['profit_trend'])
                ->color($netProfit >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-banknotes')
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-teal-50/50 to-teal-100/50 rounded-xl cursor-pointer',
                ]),
        ];
    }
}
