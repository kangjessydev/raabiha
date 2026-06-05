<?php

namespace App\Filament\Resources\SalesPages;

use App\Filament\Resources\SalesPages\Pages\CreateSalesPage;
use App\Filament\Resources\SalesPages\Pages\EditSalesPage;
use App\Filament\Resources\SalesPages\Pages\ListSalesPages;
use App\Filament\Resources\SalesPages\Pages\ViewSalesPage;
use App\Filament\Resources\SalesPages\Schemas\SalesPageForm;
use App\Filament\Resources\SalesPages\Schemas\SalesPageInfolist;
use App\Filament\Resources\SalesPages\Tables\SalesPagesTable;
use App\Models\SalesPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesPageResource extends Resource
{
    protected static ?string $model = SalesPage::class;

    protected static ?string $cluster = \App\Filament\Clusters\Marketing\MarketingCluster::class;
    protected static string|\UnitEnum|null $navigationGroup = 'CMS';
    protected static ?int $navigationSort = 5;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $modelLabel = 'Sales Page';
    protected static ?string $pluralModelLabel = 'Sales Page';
    public static function form(Schema $schema): Schema
    {
        return SalesPageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SalesPageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesPagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesPages::route('/'),
            'create' => CreateSalesPage::route('/create'),
            'view' => ViewSalesPage::route('/{record}'),
            'edit' => EditSalesPage::route('/{record}/edit'),
        ];
    }
}
