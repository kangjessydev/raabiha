<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends BaseWidget
{
    // Agar tidak di-discover otomatis di tempat lain jika ada auto-discovery general
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 4;
    
    protected static ?string $heading = '5 Produk Terlaris';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->select('order_items.name as id', 'order_items.name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.total) as total_rev'))
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.payment_status', 'paid')
                    ->groupBy('order_items.name')
                    ->orderByDesc('total_qty')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('total_qty')
                    ->label('Jumlah Terjual')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
                TextColumn::make('total_rev')
                    ->label('Total Omset')
                    ->money('IDR')
                    ->alignRight(),
            ]);
    }
}
