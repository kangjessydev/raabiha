<?php

namespace App\Filament\Resources;

use App\Models\PosReturn;
use BackedEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PosReturnResource extends Resource
{
    protected static ?string $model = PosReturn::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static \UnitEnum|string|null $navigationGroup = 'POS';

    protected static ?string $modelLabel = 'Riwayat Retur POS';
    protected static ?string $pluralModelLabel = 'Riwayat Retur POS';

    protected static ?int $navigationSort = 11;

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
            ->columns([
                TextColumn::make('return_number')
                    ->label('No. Retur')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('order.order_number')
                    ->label('Nota Asli')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => 'Kasir: ' . ($record->cashier->name ?? 'Kasir')),
                TextColumn::make('type')
                    ->label('Jenis Retur')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'exchange' => 'Tukar Barang',
                        'refund'   => 'Refund Uang',
                        default    => ucwords($state ?? '-'),
                    })
                    ->color(fn ($state) => match ($state) {
                        'exchange' => 'warning',
                        'refund'   => 'danger',
                        default    => 'secondary',
                    }),
                TextColumn::make('returned_subtotal')
                    ->label('Barang Retur')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('exchanged_subtotal')
                    ->label('Barang Tukar')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('net_amount')
                    ->label('Hasil Selisih')
                    ->money('IDR')
                    ->weight('bold')
                    ->color(fn ($state) => match (true) {
                        $state < 0 => 'danger',
                        $state > 0 => 'warning',
                        default    => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('refund_payment_method')
                    ->label('Metode Refund')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash'  => 'Tunai Laci',
                        'bank'  => 'Transfer Bank',
                        default => $state ? ucwords($state) : '-',
                    })
                    ->color(fn ($state) => match ($state) {
                        'cash' => 'success',
                        'bank' => 'info',
                        default => 'secondary',
                    }),
                TextColumn::make('supervisor.name')
                    ->label('Supervisor')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Waktu Retur')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis Retur')
                    ->native(false)
                    ->options([
                        'exchange' => 'Tukar Barang',
                        'refund'   => 'Pengembalian Uang (Refund)',
                    ]),
                SelectFilter::make('refund_payment_method')
                    ->label('Metode Refund')
                    ->native(false)
                    ->options([
                        'cash' => 'Tunai Laci Kasir',
                        'bank' => 'Transfer Bank',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Retur & Otorisasi')
                    ->schema([
                        TextEntry::make('return_number')->label('No. Retur')->weight('bold'),
                        TextEntry::make('order.order_number')->label('Nota Asli'),
                        TextEntry::make('type')->label('Jenis Retur')->badge()->formatStateUsing(fn ($state) => match ($state) {
                            'exchange' => 'Tukar Barang',
                            'refund'   => 'Refund Uang',
                            default    => ucwords($state ?? '-'),
                        }),
                        TextEntry::make('cashier.name')->label('Kasir'),
                        TextEntry::make('supervisor.name')->label('Supervisor Pengizin')->default('-'),
                        TextEntry::make('created_at')->label('Waktu Diproses')->dateTime('d M Y H:i'),
                        TextEntry::make('reason')->label('Alasan Retur / Penukaran')->columnSpanFull(),
                    ])->columns(3),

                Section::make('Item Barang yang Dikembalikan (Restock)')
                    ->schema([
                        RepeatableEntry::make('returnedItems')
                            ->label('')
                            ->schema([
                                TextEntry::make('product_name')
                                    ->label('Nama Barang')
                                    ->state(fn ($record) => $record->product ? ($record->product->name . ($record->variant ? ' - ' . $record->variant->name : '')) : '-'),
                                TextEntry::make('quantity')->label('Qty Retur'),
                                TextEntry::make('price')->label('Harga Satuan')->money('IDR'),
                                TextEntry::make('total')->label('Subtotal')->money('IDR')->weight('bold'),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Item Barang Pengganti (Tukar)')
                    ->visible(fn ($record) => $record->exchangedItems()->count() > 0)
                    ->schema([
                        RepeatableEntry::make('exchangedItems')
                            ->label('')
                            ->schema([
                                TextEntry::make('product_name')
                                    ->label('Nama Barang Pengganti')
                                    ->state(fn ($record) => $record->product ? ($record->product->name . ($record->variant ? ' - ' . $record->variant->name : '')) : '-'),
                                TextEntry::make('quantity')->label('Qty Tukar'),
                                TextEntry::make('price')->label('Harga Satuan')->money('IDR'),
                                TextEntry::make('total')->label('Subtotal')->money('IDR')->weight('bold'),
                            ])
                            ->columns(4),
                    ]),

                Section::make('Rincian Pembukuan Keuangan')
                    ->schema([
                        TextEntry::make('returned_subtotal')->label('Total Nilai Barang Retur')->money('IDR'),
                        TextEntry::make('exchanged_subtotal')->label('Total Nilai Barang Tukar')->money('IDR'),
                        TextEntry::make('net_amount')->label('Hasil Selisih')->money('IDR')->weight('bold'),
                        TextEntry::make('refund_payment_method')->label('Metode Refund')->badge(),
                        TextEntry::make('refund_bank_name')->label('Bank Refund')->default('-'),
                        TextEntry::make('refund_bank_account')->label('No. Rek / Pemilik')->default('-'),
                    ])->columns(3),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PosReturnResource\Pages\ListPosReturns::route('/'),
        ];
    }
}
