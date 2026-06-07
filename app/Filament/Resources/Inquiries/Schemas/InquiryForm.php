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
                    ->readOnly(),
                \Filament\Forms\Components\TextInput::make('phone')
                    ->label('No. Handphone')
                    ->readOnly(),
                \Filament\Forms\Components\TextInput::make('subject')
                    ->label('Subjek')
                    ->readOnly(),
                \Filament\Forms\Components\Select::make('channel')
                    ->label('Metode Kontak')
                    ->options([
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                    ])
                    ->readOnly(),
                \Filament\Forms\Components\Select::make('status')
                    ->label('Status Tiket')
                    ->options([
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'replied' => 'Dibalas',
                    ])
                    ->required(),
                \Filament\Forms\Components\Textarea::make('message')
                    ->label('Pesan')
                    ->readOnly()
                    ->columnSpanFull()
                    ->rows(5),
            ]);
    }
}
