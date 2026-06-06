<?php

namespace App\Filament\Clusters\ECommerce;

use Filament\Support\Contracts\HasLabel;

enum ECommerceNavigationGroup: string implements HasLabel
{
    case Transaksi = 'Transaksi';
    case Katalog = 'Katalog';
    case Promosi = 'Promosi';
    case Reseller = 'Reseller';
    case PengaturanToko = 'Pengaturan Checkout';

    public function getLabel(): string
    {
        return match ($this) {
            self::Transaksi => 'Transaksi',
            self::Katalog => 'Katalog',
            self::Promosi => 'Promosi',
            self::Reseller => 'Reseller',
            self::PengaturanToko => 'Pengaturan Checkout',
        };
    }
}
