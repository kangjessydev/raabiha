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

    protected function getStats(): array
    {
        $today = now()->toDateString();

        // Cache 10 detik agar tetap responsif dan tidak membebani VPS
        $data = Cache::remember('dashboard_overview_today_' . $today, 10, function () use ($today) {
            // 1. Pesanan Menunggu Pembayaran
            $pendingPayment = Order::selectRaw('COUNT(*) as count, SUM(grand_total) as amount')
                ->whereDate('created_at', $today)
                ->where('payment_status', 'pending')
                ->first();

            // 2. Pesanan Selesai
            $completed = Order::selectRaw('COUNT(*) as count, SUM(grand_total) as amount')
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->first();

            // 3. Pesanan Batal
            $cancelled = Order::selectRaw('COUNT(*) as count, SUM(grand_total) as amount')
                ->whereDate('created_at', $today)
                ->where('status', 'cancelled')
                ->first();

            // 4. Pengeluaran Hari Ini (Kas Keluar)
            $expense = Cashflow::whereDate('transaction_date', $today)
                ->where('type', 'out')
                ->sum('amount');

            // 5. Voucher digunakan
            $vouchers = Order::selectRaw('COUNT(*) as count, SUM(discount_total) as discount')
                ->whereDate('created_at', $today)
                ->whereNotNull('voucher_id')
                ->first();

            // 6. Laba Bersih
            $revenue = Cashflow::whereDate('transaction_date', $today)
                ->where('type', 'in')
                ->where('is_reversed', false)
                ->sum('amount');

            // --- Query Tren Hourly untuk Sparkline ---
            $pendingHourly = Order::selectRaw('HOUR(created_at) as hr, COUNT(*) as count')
                ->whereDate('created_at', $today)
                ->where('payment_status', 'pending')
                ->groupBy('hr')
                ->pluck('count', 'hr')
                ->toArray();

            $completedHourly = Order::selectRaw('HOUR(created_at) as hr, COUNT(*) as count')
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->groupBy('hr')
                ->pluck('count', 'hr')
                ->toArray();

            $cancelledHourly = Order::selectRaw('HOUR(created_at) as hr, COUNT(*) as count')
                ->whereDate('created_at', $today)
                ->where('status', 'cancelled')
                ->groupBy('hr')
                ->pluck('count', 'hr')
                ->toArray();

            $hourlyExpenses = Cashflow::selectRaw('HOUR(created_at) as hr, SUM(amount) as amount')
                ->whereDate('transaction_date', $today)
                ->where('type', 'out')
                ->groupBy('hr')
                ->pluck('amount', 'hr')
                ->toArray();

            $voucherHourly = Order::selectRaw('HOUR(created_at) as hr, COUNT(*) as count')
                ->whereDate('created_at', $today)
                ->whereNotNull('voucher_id')
                ->groupBy('hr')
                ->pluck('count', 'hr')
                ->toArray();

            $hourlyRevenue = Cashflow::selectRaw('HOUR(created_at) as hr, SUM(amount) as amount')
                ->whereDate('transaction_date', $today)
                ->where('type', 'in')
                ->where('is_reversed', false)
                ->groupBy('hr')
                ->pluck('amount', 'hr')
                ->toArray();

            $currentHour = (int) now()->format('H');
            $pendingTrend = [];
            $completedTrend = [];
            $cancelledTrend = [];
            $expenseTrend = [];
            $voucherTrend = [];
            $profitTrend = [];

            for ($i = 5; $i >= 0; $i--) {
                $h = ($currentHour - $i + 24) % 24;
                $pendingTrend[] = (int) ($pendingHourly[$h] ?? 0);
                $completedTrend[] = (int) ($completedHourly[$h] ?? 0);
                $cancelledTrend[] = (int) ($cancelledHourly[$h] ?? 0);
                $expenseTrend[] = (int) ($hourlyExpenses[$h] ?? 0);
                $voucherTrend[] = (int) ($voucherHourly[$h] ?? 0);
                $profitTrend[] = (int) (($hourlyRevenue[$h] ?? 0) - ($hourlyExpenses[$h] ?? 0));
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
        $netProfit = $data['revenue'] - $data['expense'];

        return [
            // 1. Pesanan Menunggu Pembayaran
            Stat::make('Pesanan Menunggu Pembayaran', $data['pending_payment_count'] . ' Pesanan')
                ->description('Nominal: ' . $fmt($data['pending_payment_amount']))
                ->descriptionIcon('heroicon-m-clock')
                ->chart($data['pending_trend'])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'stat-card-warning',
                ]),

            // 2. Pesanan Selesai
            Stat::make('Pesanan Selesai', $data['completed_count'] . ' Pesanan')
                ->description('Nominal: ' . $fmt($data['completed_amount']))
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart($data['completed_trend'])
                ->color('success')
                ->extraAttributes([
                    'class' => 'stat-card-success',
                ]),

            // 3. Pesanan Batal
            Stat::make('Pesanan Batal', $data['cancelled_count'] . ' Pesanan')
                ->description('Nominal: ' . $fmt($data['cancelled_amount']))
                ->descriptionIcon('heroicon-m-x-circle')
                ->chart($data['cancelled_trend'])
                ->color('danger')
                ->extraAttributes([
                    'class' => 'stat-card-danger',
                ]),

            // 4. Pengeluaran Hari Ini
            Stat::make('Pengeluaran Hari Ini', $fmt($data['expense']))
                ->description('Diambil dari buku kas keluar')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart($data['expense_trend'])
                ->color('danger')
                ->extraAttributes([
                    'class' => 'stat-card-expense',
                ]),

            // 5. Voucher Digunakan
            Stat::make('Voucher Digunakan', $data['vouchers_count'] . ' Voucher')
                ->description('Total diskon: ' . $fmt($data['vouchers_discount']))
                ->descriptionIcon('heroicon-m-ticket')
                ->chart($data['voucher_trend'])
                ->color('info')
                ->extraAttributes([
                    'class' => 'stat-card-info',
                ]),

            // 6. Laba Bersih
            Stat::make('Laba Bersih', $fmt($netProfit))
                ->description('Masuk: ' . $fmt($data['revenue']) . ' | Keluar: ' . $fmt($data['expense']))
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($data['profit_trend'])
                ->color($netProfit >= 0 ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => 'stat-card-netprofit',
                ]),
        ];
    }
}
