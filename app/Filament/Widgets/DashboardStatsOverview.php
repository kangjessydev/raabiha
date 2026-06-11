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
            ];
        });

        $fmt = fn (int $value): string => 'Rp ' . number_format($value, 0, ',', '.');
        $netProfit = $data['revenue'] - $data['expense'];

        return [
            // 1. Pesanan Menunggu Pembayaran
            Stat::make('Pesanan Menunggu Pembayaran', $data['pending_payment_count'] . ' Pesanan')
                ->description('Nominal: ' . $fmt($data['pending_payment_amount']))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // 2. Pesanan Selesai
            Stat::make('Pesanan Selesai', $data['completed_count'] . ' Pesanan')
                ->description('Nominal: ' . $fmt($data['completed_amount']))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            // 3. Pesanan Batal
            Stat::make('Pesanan Batal', $data['cancelled_count'] . ' Pesanan')
                ->description('Nominal: ' . $fmt($data['cancelled_amount']))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            // 4. Pengeluaran Hari Ini
            Stat::make('Pengeluaran Hari Ini', $fmt($data['expense']))
                ->description('Diambil dari buku kas keluar')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            // 5. Voucher Digunakan
            Stat::make('Voucher Digunakan', $data['vouchers_count'] . ' Voucher')
                ->description('Total diskon: ' . $fmt($data['vouchers_discount']))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),

            // 6. Laba Bersih
            Stat::make('Laba Bersih', $fmt($netProfit))
                ->description('Masuk: ' . $fmt($data['revenue']) . ' | Keluar: ' . $fmt($data['expense']))
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($netProfit >= 0 ? 'success' : 'danger'),
        ];
    }
}
