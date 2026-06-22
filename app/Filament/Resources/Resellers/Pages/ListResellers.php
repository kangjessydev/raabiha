<?php

namespace App\Filament\Resources\Resellers\Pages;

use App\Filament\Resources\Resellers\ResellerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListResellers extends ListRecords
{
    protected static string $resource = ResellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => ResellerResource::canCreate()),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Baru')
                ->badge($this->getModel()::whereNot('reseller_status', 'none')->whereNot('reseller_status', null)->count()),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn ($query) => $query->where('reseller_status', 'pending'))
                ->badge($this->getModel()::where('reseller_status', 'pending')->count()),
            'active' => Tab::make('Aktif')
                ->modifyQueryUsing(fn ($query) => $query->where('reseller_status', 'active'))
                ->badge($this->getModel()::where('reseller_status', 'active')->count()),
            'rejected' => Tab::make('Berhenti')
                ->modifyQueryUsing(fn ($query) => $query->where('reseller_status', 'rejected'))
                ->badge($this->getModel()::where('reseller_status', 'rejected')->count()),
        ];
    }
}
