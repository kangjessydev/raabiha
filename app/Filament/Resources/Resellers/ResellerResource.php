<?php

namespace App\Filament\Resources\Resellers;

use App\Filament\Clusters\ECommerce\ECommerceCluster;
use App\Filament\Resources\Resellers\Pages\CreateReseller;
use App\Filament\Resources\Resellers\Pages\EditReseller;
use App\Filament\Resources\Resellers\Pages\ListResellers;
use App\Filament\Resources\Resellers\Schemas\ResellerForm;
use App\Filament\Resources\Resellers\Tables\ResellersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResellerResource extends Resource
{
    protected static ?string $cluster = ECommerceCluster::class;
    protected static \UnitEnum|string|null $navigationGroup = \App\Filament\Clusters\ECommerce\ECommerceNavigationGroup::Reseller;
    protected static ?int $navigationSort = 1;
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?string $modelLabel = 'Reseller';
    protected static ?string $pluralModelLabel = 'Daftar Reseller';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNot('reseller_status', 'none');
    }

    public static function form(Schema $schema): Schema
    {
        return ResellerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResellersTable::configure($table);
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
            'index' => ListResellers::route('/'),
            'create' => CreateReseller::route('/create'),
            'edit' => EditReseller::route('/{record}/edit'),
        ];
    }
}
