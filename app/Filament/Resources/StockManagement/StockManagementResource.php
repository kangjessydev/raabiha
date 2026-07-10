<?php

namespace App\Filament\Resources\StockManagement;

use App\Filament\Clusters\ECommerce\ECommerceCluster;
use App\Filament\Clusters\ECommerce\ECommerceNavigationGroup;
use App\Filament\Resources\StockManagement\Pages\ListStockManagement;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StockManagementResource extends Resource
{
    protected static ?string $cluster = ECommerceCluster::class;
    protected static ?int $navigationSort = 25;
    protected static \UnitEnum|string|null $navigationGroup = ECommerceNavigationGroup::Katalog;

    protected static ?string $model = Product::class;

    protected static ?string $modelLabel = 'Manajemen Stok';
    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'owner', 'logistics']);
    }

    protected static ?string $pluralModelLabel = 'Manajemen Stok';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    public static function getNavigationBadge(): ?string
    {
        $defaultMin = (int) (\App\Models\SiteSetting::where('key', 'default_minimum_stock')->value('value') ?? 5);

        // Count products without variants that are low stock
        $noVariantsLowStock = static::getModel()::where('has_variants', false)
            ->whereRaw('stock <= COALESCE(minimum_stock, ?)', [$defaultMin])
            ->count();

        // Count products with variants that have at least one variant that is low stock
        $hasVariantsLowStock = static::getModel()::where('has_variants', true)
            ->whereHas('variants', function ($query) use ($defaultMin) {
                $query->whereRaw('stock <= COALESCE(minimum_stock, ?)', [$defaultMin]);
            })
            ->count();

        $totalLowStock = $noVariantsLowStock + $hasVariantsLowStock;

        return $totalLowStock ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() !== null ? 'danger' : 'gray';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\StockManagement\Tables\StockManagementTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockManagement::route('/'),
        ];
    }
}
