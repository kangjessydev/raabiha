<?php

namespace App\Filament\Resources\Vouchers\Pages;

use App\Filament\Resources\Vouchers\VoucherResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVoucher extends CreateRecord
{
    protected static string $resource = VoucherResource::class;

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster()) && $cluster::shouldRegisterSubNavigation()) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
