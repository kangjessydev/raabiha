<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductPerformanceWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.product-performance-widget';

    protected int | string | array $columnSpan = 'full';

    public ?array $filters = null;

    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    public function updateFilters($filters)
    {
        $this->filters = $filters;
        $this->dispatch('$refresh');
    }

    public function getViewData(): array
    {
        $from = $this->filters['created_from'] ?? now()->subDays(29)->toDateString();
        $until = $this->filters['created_until'] ?? now()->toDateString();
        $channel = $this->filters['channel'] ?? 'all';

        // 1. Produk Paling Laku (Top Sellers)
        $topProductsQuery = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])
            ->where('orders.payment_status', 'paid');

        if ($channel === 'online') {
            $topProductsQuery->whereNull('orders.pos_session_id');
        } elseif ($channel === 'pos') {
            $topProductsQuery->whereNotNull('orders.pos_session_id');
        }

        $topProducts = $topProductsQuery
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.subtotal) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 2. Produk Paling Lambat Laku / Dead Stock dalam rentang terpilih
        $slowProducts = Product::query()
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) use ($from, $until, $channel) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->whereBetween('orders.created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])
                    ->where('orders.payment_status', 'paid');
                if ($channel === 'online') {
                    $join->whereNull('orders.pos_session_id');
                } elseif ($channel === 'pos') {
                    $join->whereNotNull('orders.pos_session_id');
                }
            })
            ->select('products.id', 'products.name', 'products.stock', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_qty'))
            ->groupBy('products.id', 'products.name', 'products.stock')
            ->orderBy('total_qty', 'asc')
            ->orderBy('products.stock', 'desc')
            ->limit(5)
            ->get();

        return [
            'topProducts' => $topProducts,
            'slowProducts' => $slowProducts,
        ];
    }
}
