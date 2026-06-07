<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                \Awcodes\Curator\Components\Tables\CuratorColumn::make('images')
                    ->label('Foto')
                    ->circular()
                    ->ring(2)
                    ->overlap(4)
                    ->limit(3),
                TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->limit(30)
                    ->url(fn (\App\Models\Product $record): string => url('/product/' . $record->slug))
                    ->openUrlInNewTab()
                    ->color('primary'),
                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('reseller_price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('has_variants')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
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
                \Filament\Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('view_frontend')
                    ->label('Lihat di Toko')
                    ->icon('heroicon-o-eye')
                    ->url(fn (\App\Models\Product $record): string => url('/product/' . $record->slug))
                    ->openUrlInNewTab()
                    ->color('info'),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
