<?php

namespace App\Filament\Resources\ProductReviews\Pages;

use App\Filament\Resources\ProductReviews\ProductReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListProductReviews extends ListRecords
{
    protected static string $resource = ProductReviewResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge($this->getModel()::count()),
            'pending' => Tab::make('Perlu Moderasi')
                ->modifyQueryUsing(fn ($query) => $query->where('is_approved', false))
                ->badge($this->getModel()::where('is_approved', false)->count())
                ->badgeColor('warning'),
            'approved' => Tab::make('Disetujui')
                ->modifyQueryUsing(fn ($query) => $query->where('is_approved', true))
                ->badge($this->getModel()::where('is_approved', true)->count())
                ->badgeColor('success'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
