<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Product;
use Filament\Support\Colors\Color;

class LaporanStok extends Page implements HasTable
{
    use HasPageShield;
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';
    protected static \UnitEnum|string|null $navigationGroup = 'Laporan & Keuangan';
    protected static ?string $title = 'Laporan Stok & Valuasi Inventaris';
    protected static ?string $navigationLabel = 'Laporan Stok';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.laporan-stok';

    public function getViewData(): array
    {
        $products = Product::all();
        
        $totalHppValuation = $products->sum(fn ($p) => ($p->purchase_price ?? 0) * $p->stock);
        $totalRetailValuation = $products->sum(fn ($p) => ($p->price ?? 0) * $p->stock);
        $lowStockCount = $products->filter(fn ($p) => $p->stock <= 5 && $p->stock > 0)->count();
        $outOfStockCount = $products->filter(fn ($p) => $p->stock <= 0)->count();

        return [
            'totalHppValuation' => $totalHppValuation,
            'totalRetailValuation' => $totalRetailValuation,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query())
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge(),
                TextColumn::make('stock')
                    ->label('Sisa Stok')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('purchase_price')
                    ->label('Harga Modal (HPP)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_valuasi_hpp')
                    ->label('Total Valuasi HPP')
                    ->state(fn ($record) => ($record->purchase_price ?? 0) * $record->stock)
                    ->money('IDR')
                    ->sortable(),
            ]);
    }
}
