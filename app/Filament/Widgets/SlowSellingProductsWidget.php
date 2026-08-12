<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SlowSellingProductsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = '🐢 Produk Paling Lambat Laku (Slow Movers)';

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

        $query = Product::query()
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
                    ->color('danger')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' Pcs'),
                TextColumn::make('stock')
                    ->label('Sisa Stok')
                    ->badge()
                    ->color(fn ($state) => $state <= 5 ? 'warning' : 'secondary')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' Unit'),
            ])
            ->paginated(false);
    }
}
