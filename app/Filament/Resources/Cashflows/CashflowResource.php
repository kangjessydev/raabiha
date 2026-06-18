<?php

namespace App\Filament\Resources\Cashflows;

use App\Filament\Resources\Cashflows\Pages\CreateCashflow;
use App\Filament\Resources\Cashflows\Pages\EditCashflow;
use App\Filament\Resources\Cashflows\Pages\ListCashflows;
use App\Filament\Resources\Cashflows\Schemas\CashflowForm;
use App\Filament\Resources\Cashflows\Tables\CashflowsTable;
use App\Models\Cashflow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashflowResource extends Resource
{
    protected static ?string $cluster = \App\Filament\Clusters\ECommerce\ECommerceCluster::class;
    protected static ?int $navigationSort = 3;
    protected static \UnitEnum|string|null $navigationGroup = \App\Filament\Clusters\ECommerce\ECommerceNavigationGroup::Transaksi;

    protected static ?string $modelLabel = 'Buku Kas';
    protected static ?string $pluralModelLabel = 'Buku Kas';

    protected static ?string $model = Cashflow::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Schema $schema): Schema
    {
        return CashflowForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashflowsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\Cashflows\Widgets\CashflowStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashflows::route('/'),
            'create' => CreateCashflow::route('/create'),
            'edit' => EditCashflow::route('/{record}/edit'),
        ];
    }
}
