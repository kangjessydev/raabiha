<?php

namespace App\Filament\Resources\PaymentMethods\Pages;

use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $config = is_array($data['config'] ?? null) ? $data['config'] : [];
        $config['availability'] = $data['availability'] ?? 'both';
        unset($data['availability']);
        $data['config'] = $config;
        return $data;
    }

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster()) && $cluster::shouldRegisterSubNavigation()) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
