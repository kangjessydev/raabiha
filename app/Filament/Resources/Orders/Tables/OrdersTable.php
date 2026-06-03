<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('ID Pesanan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('user_type')
                    ->label('Tipe Pelanggan')
                    ->badge()
                    ->color(fn ($record) => $record->user_id ? 'success' : 'warning')
                    ->state(fn ($record) => $record->user_id ? 'Member' : 'Guest'),
                TextColumn::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->state(function ($record) {
                        return $record->user_id ? $record->user->name : ($record->shipping_address['name'] ?? 'Guest User');
                    })
                    ->searchable(['user.name'])
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'info',
                        'packed' => 'primary',
                        'sent' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    })
                    ->searchable(),
                TextColumn::make('grand_total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_member')
                    ->label('Tipe Pelanggan')
                    ->placeholder('Semua Pelanggan')
                    ->trueLabel('Hanya Member')
                    ->falseLabel('Hanya Guest')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('user_id'),
                        false: fn ($query) => $query->whereNull('user_id'),
                        blank: fn ($query) => $query,
                    )
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
