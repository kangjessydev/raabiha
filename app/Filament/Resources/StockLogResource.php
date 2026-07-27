<?php

namespace App\Filament\Resources;

use App\Models\StockLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockLogResource extends Resource
{
    protected static ?string $model = StockLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static \UnitEnum|string|null $navigationGroup = 'Katalog';

    protected static ?string $modelLabel = 'Log & Audit Stok';
    protected static ?string $pluralModelLabel = 'Log & Audit Stok';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu Mutasi')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('variant.name')
                    ->label('Varian')
                    ->badge()
                    ->placeholder('-')
                    ->color('info')
                    ->searchable(),
                TextColumn::make('product.channel_visibility')
                    ->label('Kanal')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pos_only'    => 'POS Kasir',
                        'online_only' => 'E-Commerce',
                        default       => ucwords($state ?? '-'),
                    })
                    ->color(fn ($state) => match ($state) {
                        'pos_only'    => 'warning',
                        'online_only' => 'info',
                        default       => 'secondary',
                    }),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'in'    => 'Stok Masuk (+)',
                        'out'   => 'Stok Keluar (-)',
                        default => ucwords($state ?? '-'),
                    })
                    ->color(fn ($state) => match ($state) {
                        'in'  => 'success',
                        'out' => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('quantity_change')
                    ->label('Perubahan')
                    ->formatStateUsing(fn ($state) => ($state > 0 ? "+{$state}" : "{$state}") . ' pcs')
                    ->weight('bold')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('stock_before_after')
                    ->label('Stok (Awal → Akhir)')
                    ->state(fn ($record) => "{$record->quantity_before} → {$record->quantity_after} pcs")
                    ->color('gray'),
                TextColumn::make('reason')
                    ->label('Alasan Perubahan')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        $reasons = StockLog::getReasonOptions();
                        return $reasons[$state] ?? ucwords(str_replace('_', ' ', $state));
                    })
                    ->color('secondary')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Penanggung Jawab')
                    ->default('Sistem / Otomatis')
                    ->searchable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->default('-')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('reason')
                    ->label('Alasan Perubahan')
                    ->native(false)
                    ->options(fn () => StockLog::getReasonOptions()),
                SelectFilter::make('type')
                    ->label('Jenis Mutasi')
                    ->native(false)
                    ->options([
                        'in'  => 'Stok Masuk (+)',
                        'out' => 'Stok Keluar (-)',
                    ]),
                SelectFilter::make('channel_visibility')
                    ->label('Kanal Penjualan')
                    ->relationship('product', 'channel_visibility')
                    ->native(false)
                    ->options([
                        'pos_only'    => 'POS Kasir Saja (Toko Fisik)',
                        'online_only' => 'E-Commerce Saja (Website Online)',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => StockLogResource\Pages\ListStockLogs::route('/'),
        ];
    }
}
