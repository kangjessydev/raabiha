<?php

namespace App\Filament\Resources\RefundRequests\Pages;

use App\Filament\Resources\RefundRequests\RefundRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ManageRefundRequests extends ManageRecords
{
    protected static string $resource = RefundRequestResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge($this->getModel()::count()),
            'pending' => Tab::make('Menunggu')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending'))
                ->badge($this->getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),
            'approved' => Tab::make('Disetujui')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'approved'))
                ->badge($this->getModel()::where('status', 'approved')->count())
                ->badgeColor('info'),
            'rejected' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'rejected'))
                ->badge($this->getModel()::where('status', 'rejected')->count())
                ->badgeColor('danger'),
            'completed' => Tab::make('Selesai')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'completed'))
                ->badge($this->getModel()::where('status', 'completed')->count())
                ->badgeColor('success'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\RefundRequests\Widgets\RefundStatsWidget::class,
        ];
    }
}
