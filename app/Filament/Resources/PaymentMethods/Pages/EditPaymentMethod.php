<?php

namespace App\Filament\Resources\PaymentMethods\Pages;

use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentMethod extends EditRecord
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $config = is_array($data['config'] ?? null) ? $data['config'] : [];
        $data['availability'] = $config['availability'] ?? 'both';
        unset($config['availability']);
        $data['config'] = $config;
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
