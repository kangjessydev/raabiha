<?php

namespace App\Filament\Resources\Comments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post.title')
                    ->label('Artikel Blog')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('customer_name')
                    ->label('Nama Komentator')
                    ->searchable()
                    ->description(fn ($record) => $record->customer_email),
                TextColumn::make('comment')
                    ->label('Komentar')
                    ->searchable()
                    ->wrap(),
                IconColumn::make('is_approved')
                    ->label('Disetujui')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
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
            ])
            ->poll('15s')
            ->defaultSort('created_at', 'desc');
    }
}
