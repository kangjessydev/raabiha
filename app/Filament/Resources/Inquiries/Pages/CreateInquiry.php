<?php

namespace App\Filament\Resources\Inquiries\Pages;

use App\Filament\Resources\Inquiries\InquiryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInquiry extends CreateRecord
{
    protected static string $resource = InquiryResource::class;

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster()) && $cluster::shouldRegisterSubNavigation()) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
