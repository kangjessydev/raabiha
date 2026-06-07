<?php

namespace App\Filament\Resources\Inquiries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class InquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pengirim')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('channel')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'info',
                        'whatsapp' => 'success',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'replied' => 'Dibalas',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'replied' => 'Dibalas',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('channel')
                    ->options([
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
