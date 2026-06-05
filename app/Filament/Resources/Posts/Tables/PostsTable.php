<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                CuratorColumn::make('image')
                    ->label('Sampul')
                    ->size(50)
                    ->circular(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_published')
                    ->label('Terbit')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Tgl Terbit')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('author.name')
                    ->label('Penulis')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('post_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->placeholder('Semua Artikel')
                    ->trueLabel('Sudah Terbit')
                    ->falseLabel('Draft (Belum Terbit)'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                //
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
