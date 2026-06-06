<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Voucher')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
                TextColumn::make('discount_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'success',
                        'percent' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fixed' => 'Nominal',
                        'percent' => 'Persen',
                        default => $state,
                    }),
                TextColumn::make('discount_amount')
                    ->label('Nilai Diskon')
                    ->formatStateUsing(fn ($record) => $record->discount_type === 'percent' 
                        ? rtrim(rtrim(number_format($record->discount_amount, 2), '0'), '.') . '%' 
                        : 'Rp ' . number_format($record->discount_amount, 0, ',', '.')),
                TextColumn::make('min_purchase')
                    ->label('Min. Belanja')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('used_count')
                    ->label('Terpakai')
                    ->formatStateUsing(fn ($record) => $record->used_count . ($record->max_uses ? ' / ' . $record->max_uses : ' (Tanpa Batas)')),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Kedaluwarsa')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
