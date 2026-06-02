<?php

namespace App\Filament\Resources\Salespages;

use App\Filament\Clusters\Marketing\MarketingCluster;
use App\Filament\Resources\Salespages\Pages\CreateSalespage;
use App\Filament\Resources\Salespages\Pages\EditSalespage;
use App\Filament\Resources\Salespages\Pages\ListSalespages;
use App\Filament\Resources\Salespages\Schemas\SalespageForm;
use App\Filament\Resources\Salespages\Tables\SalespagesTable;
use App\Models\Salespage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalespageResource extends Resource
{
    protected static ?string $cluster = MarketingCluster::class;
    protected static \UnitEnum|string|null $navigationGroup = 'CMS';
    protected static ?int $navigationSort = 2;
    protected static ?string $model = Salespage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $modelLabel = 'Sales Page';
    protected static ?string $pluralModelLabel = 'Sales Page';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return SalespageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalespagesTable::configure($table);
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
            'index' => ListSalespages::route('/'),
            'create' => CreateSalespage::route('/create'),
            'edit' => EditSalespage::route('/{record}/edit'),
        ];
    }
}
