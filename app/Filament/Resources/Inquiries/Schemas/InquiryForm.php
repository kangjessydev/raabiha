<?php

namespace App\Filament\Resources\Inquiries\Schemas;

use Filament\Schemas\Schema;

class InquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->readOnly(),
                \Filament\Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->readOnly()
                    ->suffixAction(
                        \Filament\Actions\Action::make('copyEmail')
                            ->icon('heroicon-m-clipboard')
                            ->tooltip('Salin Email')
                            ->action(function ($state, $livewire) {
                                if ($state) {
                                    $livewire->js('window.navigator.clipboard.writeText("' . addslashes($state) . '")');
                                    \Filament\Notifications\Notification::make()
                                        ->title('Email berhasil disalin')
                                        ->success()
                                        ->send();
                                }
                            })
                    ),
                \Filament\Forms\Components\TextInput::make('phone')
                    ->label('No. Handphone / WhatsApp')
                    ->readOnly()
                    ->suffixAction(
                        \Filament\Actions\Action::make('copyPhone')
                            ->icon('heroicon-m-clipboard')
                            ->tooltip('Salin No. Handphone')
                            ->action(function ($state, $livewire) {
                                if ($state) {
                                    $livewire->js('window.navigator.clipboard.writeText("' . addslashes($state) . '")');
                                    \Filament\Notifications\Notification::make()
                                        ->title('Nomor berhasil disalin')
                                        ->success()
                                        ->send();
                                }
                            })
                    ),
                \Filament\Forms\Components\TextInput::make('subject')
                    ->label('Subjek')
                    ->readOnly(),
                \Filament\Forms\Components\Select::make('channel')
                    ->label('Metode Kontak')
                    ->options([
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                    ])
                    ->native(false)
                    ->disabled(),
                \Filament\Forms\Components\Select::make('status')
                    ->label('Status Tiket')
                    ->options([
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'replied' => 'Dibalas',
                    ])
                    ->native(false)
                    ->required(),
                \Filament\Forms\Components\Textarea::make('message')
                    ->label('Pesan')
                    ->readOnly()
                    ->columnSpanFull()
                    ->rows(5),
            ]);
    }
}
