<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Models\Cashflow;
use App\Models\Cart;
use Illuminate\Support\Carbon;

class LaporanBisnisOverview extends BaseWidget
{
    protected static bool $isDiscovered = false;

    public ?array $filters = null;

    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    public function updateFilters($filters)
    {
        $this->filters = $filters;
        $this->dispatch('$refresh');
    }

    protected function getStats(): array
    {
        $from = $this->filters['created_from'] ?? now()->subDays(29)->toDateString();
        $until = $this->filters['created_until'] ?? now()->toDateString();

        // 1. Jumlah Transaksi Lunas
        $totalPaidOrders = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])
            ->count();

        // 2. AOV (Average Order Value)
        $completedOrdersQuery = Order::where('status', 'completed')
            ->whereBetween('created_at', [$from . ' 00:00:00', $until . ' 23:59:59']);
        $totalSpend = (float) $completedOrdersQuery->sum('grand_total');
        $totalCompletedCount = $completedOrdersQuery->count();
        $aov = $totalCompletedCount > 0 ? $totalSpend / $totalCompletedCount : 0;

        // 3. Omset / Total Pendapatan Kotor
        $totalRevenue = (float) Cashflow::where('type', 'in')
            ->where('is_reversed', false)
            ->whereBetween('transaction_date', [$from, $until])
            ->sum('amount');

        // 4. Total Pengeluaran
        $totalExpense = (float) Cashflow::where('type', 'out')
            ->whereBetween('transaction_date', [$from, $until])
            ->sum('amount');

        // 4b. Total HPP (Harga Pokok Penjualan)
        $hpp = (float) Order::where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(order_items.purchase_price, product_variants.purchase_price, products.purchase_price, 0) * order_items.quantity'));

        // 4c. Laba Kotor
        $grossProfit = $totalRevenue - $hpp;

        // 5. Laba Bersih
        $netProfit = $grossProfit - $totalExpense;

        // 6. Net Profit Margin (%)
        $netProfitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // 7. Repeat Order Rate (%)
        $customersWithOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->pluck('count')
            ->toArray();
        $totalCustomers = count($customersWithOrders);
        $repeatCustomers = collect($customersWithOrders)->filter(fn($c) => $c >= 2)->count();
        $repeatOrderRate = $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0;

        // 8. CLV (Customer Lifetime Value)
        $clv = $totalCustomers > 0 ? $totalSpend / $totalCustomers : 0;

        // 9. Cart Abandonment Rate
        $totalCartsCount = Cart::whereBetween('created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])->count();
        $abandonedCartsCount = Cart::whereHas('items')
            ->whereBetween('created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])
            ->where('updated_at', '<', now()->subHours(24))
            ->count();
        $cartAbandonmentRate = $totalCartsCount > 0 ? ($abandonedCartsCount / $totalCartsCount) * 100 : 0;

        // Format helpers
        $formatRupiah = fn($val) => 'Rp ' . number_format($val, 0, ',', '.');

        return [
            Stat::make('Pendapatan Kotor (Omset)', $formatRupiah($totalRevenue))
                ->description('HPP: ' . $formatRupiah($hpp) . ' • Laba Kotor: ' . $formatRupiah($grossProfit))
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->extraAttributes(['class' => 'stat-card-success']),

            Stat::make('Laba Bersih', $formatRupiah($netProfit))
                ->description('Margin Profit: ' . round($netProfitMargin, 1) . '% • Operasional: ' . $formatRupiah($totalExpense))
                ->descriptionIcon('heroicon-o-presentation-chart-line')
                ->color('primary')
                ->extraAttributes(['class' => 'stat-card-primary']),

            Stat::make('Repeat Order Rate', round($repeatOrderRate, 1) . '%')
                ->description('CLV: ' . $formatRupiah($clv) . ' • Cart Abandonment: ' . round($cartAbandonmentRate, 1) . '%')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('info')
                ->extraAttributes(['class' => 'stat-card-info']),
        ];
    }
}
