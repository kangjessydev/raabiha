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
                    ->searchable(),
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
                        'in'  => '💰 Cash In (Masuk)',
                        'out' => '💸 Cash Out (Keluar)',
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
                        \Filament\Schemas\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Schemas\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('transaction_date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('transaction_date', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
