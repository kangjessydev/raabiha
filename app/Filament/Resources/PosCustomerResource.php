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

    protected static \UnitEnum|string|null $navigationGroup = 'Pelanggan & Reseller';

    protected static ?string $navigationLabel = 'Daftar Pelanggan';
    protected static ?string $modelLabel = 'Pelanggan';
    protected static ?string $pluralModelLabel = 'Daftar Pelanggan';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'phone';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        /** @var \App\Models\PosCustomer $record */
        return [
            'Nama' => $record->name ?? 'Pelanggan POS',
            'Stempel' => $record->stamp_count . ' cap',
            'Saldo Poin' => number_format($record->points_balance, 0, ',', '.') . ' poin',
        ];
    }

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
                    ->label('Kartu Selesai (9 Cap)')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} Kartu" : "0 Kartu")
                    ->sortable(),
                TextColumn::make('total_spent')
                    ->label('Total Belanja')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_debt')
                    ->label('Sisa Kasbon')
                    ->formatStateUsing(fn ($record) => $record->total_debt > 0 ? 'Rp ' . number_format($record->total_debt, 0, ',', '.') : 'Lunas')
                    ->badge()
                    ->color(fn ($record) => $record->total_debt > 0 ? 'danger' : 'gray'),
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
