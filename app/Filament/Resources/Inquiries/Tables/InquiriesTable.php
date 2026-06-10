<?php

namespace App\Filament\Resources\Inquiries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class InquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->recordClasses(fn (\Illuminate\Database\Eloquent\Model $record) => match ($record->status) {
                'new' => 'bg-emerald-50/50 dark:bg-emerald-950/10 font-semibold',
                default => null,
            })
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pengirim')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin')
                    ->hidden(fn ($livewire) => $livewire->activeTab === 'whatsapp'),
                \Filament\Tables\Columns\TextColumn::make('phone')
                    ->label('WhatsApp / No. HP')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor disalin')
                    ->hidden(fn ($livewire) => $livewire->activeTab === 'email'),
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
                    })
                    ->hidden(fn ($livewire) => in_array($livewire->activeTab, ['email', 'whatsapp'])),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'success',
                        'read' => 'gray',
                        'replied' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'replied' => 'Dibalas',
                        default => $state,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'replied' => 'Dibalas',
                    ])
                    ->native(false),
                \Filament\Tables\Filters\SelectFilter::make('channel')
                    ->options([
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->before(function (\Illuminate\Database\Eloquent\Model $record) {
                        if ($record->status === 'new') {
                            $record->update(['status' => 'read']);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
