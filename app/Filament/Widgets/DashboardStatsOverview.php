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
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $month     = now()->format('Y-m');

        // --- Cache 2 menit: cukup fresh untuk operasional ---
        $data = Cache::remember('dashboard_overview_' . $today, 120, function () use ($today, $yesterday, $month) {
            // Penjualan hari ini & kemarin (dari cashflows, source=order, aktif)
            $salesRow = Cashflow::selectRaw("
                SUM(CASE WHEN transaction_date = ? THEN amount ELSE 0 END) as today_sales,
                SUM(CASE WHEN transaction_date = ? THEN amount ELSE 0 END) as yesterday_sales,
                SUM(CASE WHEN DATE_FORMAT(transaction_date, '%Y-%m') = ? THEN amount ELSE 0 END) as month_sales
            ", [$today, $yesterday, $month])
                ->where('type', 'in')
                ->where('source', 'order')
                ->where('is_reversed', false)
                ->first();

            // Pengeluaran hari ini & kemarin (manual cash out)
            $expenseRow = Cashflow::selectRaw("
                SUM(CASE WHEN transaction_date = ? THEN amount ELSE 0 END) as today_expense,
                SUM(CASE WHEN transaction_date = ? THEN amount ELSE 0 END) as yesterday_expense
            ", [$today, $yesterday])
                ->where('type', 'out')
                ->where('source', 'manual')
                ->first();

            // Pesanan pending & processing
            $pendingOrders = Order::whereIn('status', ['pending', 'processing'])->count();
            $todayNewOrders = Order::whereDate('created_at', $today)->count();

            // Total pesanan lunas bulan ini untuk hitung AOV
            $monthOrdersCount = Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('payment_status', 'paid')
                ->count();

            return [
                'today_sales'      => (int) ($salesRow->today_sales ?? 0),
                'yesterday_sales'  => (int) ($salesRow->yesterday_sales ?? 0),
                'month_sales'      => (int) ($salesRow->month_sales ?? 0),
                'today_expense'    => (int) ($expenseRow->today_expense ?? 0),
                'yesterday_expense'=> (int) ($expenseRow->yesterday_expense ?? 0),
                'pending_orders'   => $pendingOrders,
                'today_new_orders' => $todayNewOrders,
                'month_orders_count' => $monthOrdersCount,
            ];
        });

        $todayProfit    = $data['today_sales'] - $data['today_expense'];
        $yesterdayProfit= $data['yesterday_sales'] - $data['yesterday_expense'];

        $aov = $data['month_orders_count'] > 0 ? (int) ($data['month_sales'] / $data['month_orders_count']) : 0;

        // Helper: format rupiah ringkas
        $fmt = fn (int $value): string => 'Rp ' . number_format($value, 0, ',', '.');

        // Helper: arah tren dari angka (positif = up, negatif = down)
        $trend = function (int $today, int $yesterday, bool $higherIsBetter = true): string {
            if ($yesterday === 0) return 'flat';
            return ($today >= $yesterday) === $higherIsBetter ? 'up' : 'down';
        };

        return [
            // 1. Penjualan Hari Ini
            Stat::make('Penjualan Hari Ini', $fmt($data['today_sales']))
                ->description(
                    $data['yesterday_sales'] > 0
                        ? 'Kemarin: ' . $fmt($data['yesterday_sales'])
                        : 'Belum ada data kemarin'
                )
                ->descriptionIcon(
                    $trend($data['today_sales'], $data['yesterday_sales']) === 'up'
                        ? 'heroicon-m-arrow-trending-up'
                        : 'heroicon-m-arrow-trending-down'
                )
                ->color(
                    $trend($data['today_sales'], $data['yesterday_sales']) === 'up' ? 'success' : 'warning'
                ),

            // 2. Pendapatan Bulan Ini (Net Sales)
            Stat::make('Pendapatan Bulan Ini', $fmt($data['month_sales']))
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            // 3. Rata-rata Belanja (AOV)
            Stat::make('Rata-rata Nilai Belanja (AOV)', $fmt($aov))
                ->description('Dari ' . $data['month_orders_count'] . ' transaksi lunas')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),

            // 4. Pesanan Aktif (Pending + Processing)
            Stat::make('Pesanan Menunggu Proses', (string) $data['pending_orders'])
                ->description('Masuk hari ini: ' . $data['today_new_orders'] . ' pesanan')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color($data['pending_orders'] > 0 ? 'warning' : 'success'),

            // 5. Laba Bersih Hari Ini (Sales - Expense)
            Stat::make('Laba Bersih Hari Ini', $fmt(max(0, $todayProfit)))
                ->description(
                    $todayProfit >= 0
                        ? 'Positif vs kemarin: ' . $fmt(max(0, $yesterdayProfit))
                        : 'Pengeluaran melebihi pendapatan hari ini'
                )
                ->descriptionIcon(
                    $todayProfit >= 0 ? 'heroicon-m-banknotes' : 'heroicon-m-exclamation-triangle'
                )
                ->color($todayProfit >= 0 ? 'success' : 'danger'),
        ];
    }
}
