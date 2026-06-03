<?php

namespace App\Filament\Resources\PaymentMethods;

use App\Filament\Clusters\ECommerce\ECommerceCluster;
use App\Filament\Resources\PaymentMethods\Pages\CreatePaymentMethod;
use App\Filament\Resources\PaymentMethods\Pages\EditPaymentMethod;
use App\Filament\Resources\PaymentMethods\Pages\ListPaymentMethods;
use App\Filament\Resources\PaymentMethods\Schemas\PaymentMethodForm;
use App\Filament\Resources\PaymentMethods\Tables\PaymentMethodsTable;
use App\Models\PaymentMethod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $cluster = ECommerceCluster::class;
    protected static ?int $navigationSort = 52;

    protected static \UnitEnum|string|null $navigationGroup = \App\Filament\Clusters\ECommerce\ECommerceNavigationGroup::PengaturanToko;
    
    protected static ?string $model = PaymentMethod::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $modelLabel = 'Metode Pembayaran';
    protected static ?string $pluralModelLabel = 'Metode Pembayaran';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PaymentMethodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentMethodsTable::configure($table);
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
            'index' => ListPaymentMethods::route('/'),
            'create' => CreatePaymentMethod::route('/create'),
            'edit' => EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
