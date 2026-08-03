<?php

namespace App\Filament\Resources;

use App\Models\PosCustomer;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

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
                TextColumn::make('last_visit_at')
                    ->label('Kunjungan Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('last_visit_at', 'desc')
            ->actions([
                ViewAction::make()
                    ->modalWidth('5xl')
                    ->stickyModalHeader()
                    ->stickyModalFooter(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                \Filament\Schemas\Components\Section::make('Biodata Pelanggan & Status Loyalty')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('name')->label('Nama Pelanggan')->weight('bold')->default('Pelanggan POS'),
                        \Filament\Infolists\Components\TextEntry::make('phone')->label('Nomor Telepon/HP')->weight('bold'),
                        \Filament\Infolists\Components\TextEntry::make('stamp_count')
                            ->label('Stempel Aktif')
                            ->badge()
                            ->color('success')
                            ->formatStateUsing(fn ($state) => "{$state} / 9 Cap"),
                        \Filament\Infolists\Components\TextEntry::make('points_balance')
                            ->label('Saldo Poin')
                            ->numeric()
                            ->weight('bold'),
                        \Filament\Infolists\Components\TextEntry::make('completed_cards_count')
                            ->label('Kartu Selesai (9 Cap)')
                            ->badge()
                            ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                            ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} Kartu" : "0 Kartu"),
                        \Filament\Infolists\Components\TextEntry::make('total_spent')->label('Total Akumulasi Belanja')->money('IDR'),
                        \Filament\Infolists\Components\TextEntry::make('last_visit_at')->label('Kunjungan Terakhir')->dateTime('d M Y H:i')->default('-'),
                    ])->columns(4),

                \Filament\Schemas\Components\Section::make('Riwayat Transaksi Pelanggan')
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('orders')
                            ->label('')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('order_number')
                                    ->label('No. Nota')
                                    ->weight('bold')
                                    ->fontFamily('mono'),
                                \Filament\Infolists\Components\TextEntry::make('created_at')
                                    ->label('Tanggal & Jam')
                                    ->dateTime('d M Y, H:i'),
                                \Filament\Infolists\Components\TextEntry::make('payment_method')
                                    ->label('Metode Bayar')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => strtoupper($state ?? 'TUNAI')),
                                \Filament\Infolists\Components\TextEntry::make('grand_total')
                                    ->label('Total Belanja')
                                    ->money('IDR')
                                    ->weight('bold'),
                                \Filament\Infolists\Components\TextEntry::make('payment_status')
                                    ->label('Status Bayar')
                                    ->badge()
                                    ->color(fn ($state) => $state === 'paid' ? 'success' : 'warning')
                                    ->formatStateUsing(fn ($state) => $state === 'paid' ? 'Lunas' : 'Menunggu'),
                                \Filament\Infolists\Components\TextEntry::make('items_summary')
                                    ->label('Rincian Barang')
                                    ->state(fn ($record) => $record->items
                                        ->map(fn ($i) => ($i->product_name ?? 'Produk') . ' (' . $i->quantity . 'x)')
                                        ->join(', ')),
                            ])
                            ->columns(6),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PosCustomerResource\Pages\ListPosCustomers::route('/'),
        ];
    }
}
