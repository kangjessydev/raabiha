<?php

namespace App\Filament\Resources\Cashflows\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class CashflowsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->poll('15s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Cash In',
                        'out' => 'Cash Out',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        default => 'secondary',
                    }),
                \Filament\Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Sales'       => 'Penjualan',
                        'Deposit'     => 'Deposit',
                        'Other_In'    => 'Pemasukan Lainnya',
                        'Operational' => 'Operasional',
                        'Marketing'   => 'Marketing & Iklan',
                        'Packaging'   => 'Packaging',
                        'Salary'      => 'Gaji Pegawai',
                        'Shipping'    => 'Biaya Kurir',
                        'Other_Out'   => 'Pengeluaran Lainnya',
                        'Order_Reversal' => 'Pembatalan Pesanan',
                        default       => $state,
                    }),
                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($record) => $record->type === 'in' ? 'success' : 'danger'),
                \Filament\Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Terkait Pesanan')
                    ->searchable()
                    ->url(fn ($record) => $record->order_id ? \App\Filament\Resources\Orders\OrderResource::getUrl('edit', ['record' => $record->order_id]) : null)
                    ->color('primary'),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(30)
                    ->searchable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Arus Kas')
                    ->options([
                        'in'  => 'Cash In (Masuk)',
                        'out' => 'Cash Out (Keluar)',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'Sales'       => 'Penjualan',
                        'Deposit'     => 'Deposit',
                        'Other_In'    => 'Pemasukan Lainnya',
                        'Operational' => 'Operasional',
                        'Marketing'   => 'Marketing & Iklan',
                        'Packaging'   => 'Packaging',
                        'Salary'      => 'Gaji Pegawai',
                        'Shipping'    => 'Biaya Kurir',
                        'Other_Out'   => 'Pengeluaran Lainnya',
                    ]),
                \Filament\Tables\Filters\Filter::make('transaction_date')
                    ->label('Rentang Tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('transaction_date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('transaction_date', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                // View — tersedia untuk semua entri
                \Filament\Actions\ViewAction::make()
                    ->modalHeading(fn ($record) => 'Detail Transaksi — ' . $record->transaction_date->format('d M Y'))
                    ->infolist([
                        \Filament\Schemas\Components\Section::make('Informasi Transaksi')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('transaction_date')
                                    ->label('Tanggal')
                                    ->date('d F Y'),
                                \Filament\Infolists\Components\TextEntry::make('type')
                                    ->label('Jenis')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state === 'in' ? 'Cash In (Masuk)' : 'Cash Out (Keluar)')
                                    ->color(fn ($state) => $state === 'in' ? 'success' : 'danger'),
                                \Filament\Infolists\Components\TextEntry::make('source')
                                    ->label('Sumber')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state === 'order' ? 'Dari Pesanan (Otomatis)' : 'Input Manual')
                                    ->color(fn ($state) => $state === 'order' ? 'info' : 'gray'),
                                \Filament\Infolists\Components\TextEntry::make('category')
                                    ->label('Kategori'),
                                \Filament\Infolists\Components\TextEntry::make('amount')
                                    ->label('Nominal')
                                    ->money('IDR')
                                    ->weight('bold'),
                                \Filament\Infolists\Components\TextEntry::make('is_reversed')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Dibatalkan (Reversed)' : 'Aktif')
                                    ->color(fn ($state) => $state ? 'danger' : 'success'),
                            ])->columns(3),

                        \Filament\Schemas\Components\Section::make('Terkait Pesanan')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('order.order_number')
                                    ->label('Nomor Pesanan')
                                    ->fontFamily('mono')
                                    ->copyable(),
                                \Filament\Infolists\Components\TextEntry::make('order.status')
                                    ->label('Status Pesanan')
                                    ->badge(),
                                \Filament\Infolists\Components\TextEntry::make('order.grand_total')
                                    ->label('Total Pesanan')
                                    ->money('IDR'),
                            ])->columns(3)
                            ->visible(fn ($record) => $record->order_id !== null),

                        \Filament\Schemas\Components\Section::make('Catatan')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('description')
                                    ->label('Keterangan')
                                    ->columnSpanFull(),
                                \Filament\Infolists\Components\TextEntry::make('reversal_note')
                                    ->label('Catatan Pembatalan')
                                    ->columnSpanFull()
                                    ->visible(fn ($record) => !empty($record->reversal_note)),
                            ]),
                    ]),

                // Edit — hanya untuk entri manual
                EditAction::make()
                    ->visible(fn ($record) => $record->source === 'manual'),

                // Delete — hanya untuk entri manual
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->source === 'manual'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

