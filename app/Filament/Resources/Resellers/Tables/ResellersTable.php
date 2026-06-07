<?php

namespace App\Filament\Resources\Resellers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResellersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reseller_status')
                    ->label('Status Reseller')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'reseller' => 'primary',
                        'customer' => 'secondary',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tgl Daftar')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['reseller_status' => 'active']))
                    ->visible(fn ($record) => $record->reseller_status === 'pending'),
                \Filament\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn ($record) => $record->update(['reseller_status' => 'rejected']))
                    ->visible(fn ($record) => $record->reseller_status === 'pending'),
                \Filament\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
