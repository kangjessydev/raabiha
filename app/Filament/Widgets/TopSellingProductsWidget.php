<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class TopSellingProductsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Produk Terlaris (Top Sellers)';

    public ?array $filters = null;

    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    public function updateFilters($filters)
    {
        $this->filters = $filters;
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        $from = $this->filters['created_from'] ?? now()->subDays(29)->toDateString();
        $until = $this->filters['created_until'] ?? now()->toDateString();
        $channel = $this->filters['channel'] ?? 'all';

        $query = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$from . ' 00:00:00', $until . ' 23:59:59'])
            ->where('orders.payment_status', 'paid');

        if ($channel === 'online') {
            $query->where(function ($sub) {
                $sub->whereIn('orders.source', ['ecommerce', 'online'])
                    ->orWhere(function ($s) {
                        $s->whereNull('orders.source')
                          ->whereNull('orders.pos_session_id')
                          ->whereNull('orders.cashier_id');
                    });
            });
        } elseif ($channel === 'pos') {
            $query->where(function ($sub) {
                $sub->whereIn('orders.source', ['pos', 'offline'])
                    ->orWhereNotNull('orders.pos_session_id')
                    ->orWhereNotNull('orders.cashier_id');
            });
        }

        $query->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.total) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5);

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('total_qty')
                    ->label('Terjual')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' Pcs'),
                TextColumn::make('total_revenue')
                    ->label('Total Omset')
                    ->money('IDR'),
            ])
            ->paginated(false);
    }
}
