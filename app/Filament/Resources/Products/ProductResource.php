<?php

namespace App\Filament\Resources\Products;

use App\Filament\Clusters\ECommerce\ECommerceCluster;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $cluster = ECommerceCluster::class;
    protected static ?int $navigationSort = 21;

    protected static \UnitEnum|string|null $navigationGroup = \App\Filament\Clusters\ECommerce\ECommerceNavigationGroup::Katalog;
    protected static ?string $model = Product::class;

    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';


    
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
