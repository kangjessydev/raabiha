<?php

namespace App\Filament\Resources\Inquiries\Pages;

use App\Filament\Resources\Inquiries\InquiryResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListInquiries extends ListRecords
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Pesan'),
            'email' => Tab::make('Email')
                ->modifyQueryUsing(fn ($query) => $query->where('channel', 'email')),
            'whatsapp' => Tab::make('WhatsApp')
                ->modifyQueryUsing(fn ($query) => $query->where('channel', 'whatsapp')),
        ];
    }
}
