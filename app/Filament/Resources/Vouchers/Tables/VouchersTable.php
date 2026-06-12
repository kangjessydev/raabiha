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
            ->poll('15s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Voucher')
                    ->searchable(['name', 'code'])
                    ->sortable()
                    ->disabledClick()
                    ->view('filament.tables.columns.voucher-badge'),
                TextColumn::make('discount_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'success',
                        'percent' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state, $record): string => $record->is_shipping_voucher ? 'Diskon Ongkir' : match ($state) {
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
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $totalLimit = $record->max_uses ?: '∞';
                        $userLimit = $record->max_uses_per_user ? $record->max_uses_per_user . 'x / user' : 'Tidak dibatasi';
                        
                        return '
                            <div class="flex flex-col gap-1 py-1">
                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                    ' . $record->used_count . ' <span class="text-gray-400 font-normal">/</span> ' . $totalLimit . '
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25 z" />
                                    </svg>
                                    <span>' . $userLimit . '</span>
                                </div>
                            </div>
                        ';
                    }),
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
