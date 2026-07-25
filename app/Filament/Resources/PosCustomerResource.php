<?php

namespace App\Filament\Resources;

use App\Models\PosCustomer;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosCustomerResource extends Resource
{
    protected static ?string $model = PosCustomer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static \UnitEnum|string|null $navigationGroup = 'POS';

    protected static ?string $modelLabel = 'Pelanggan Loyalti POS';
    protected static ?string $pluralModelLabel = 'Pelanggan Loyalti POS';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Pelanggan')
                    ->placeholder('Contoh: Ibu Siti')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Nomor Telepon/HP')
                    ->required()
                    ->maxLength(30),
                TextInput::make('stamp_count')
                    ->label('Jumlah Stempel Aktif')
                    ->numeric()
                    ->default(0)
                    ->required(),
                TextInput::make('points_balance')
                    ->label('Saldo Poin')
                    ->numeric()
                    ->default(0)
                    ->required(),
                TextInput::make('completed_cards_count')
                    ->label('Kartu 9 Cap Selesai')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                TextInput::make('total_visits')
                    ->label('Total Transaksi')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->default('Pelanggan POS'),
                TextColumn::make('phone')
                    ->label('Nomor HP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stamp_count')
                    ->label('Stempel Aktif')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => "{$state} / 9 Cap")
                    ->sortable(),
                TextColumn::make('points_balance')
                    ->label('Saldo Poin')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('completed_cards_count')
                    ->label('Kartu Selesai')
                    ->sortable(),
                TextColumn::make('total_spent')
                    ->label('Total Belanja')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('last_visit_at')
                    ->label('Kunjungan Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('last_visit_at', 'desc')
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PosCustomerResource\Pages\ListPosCustomers::route('/'),
        ];
    }
}
