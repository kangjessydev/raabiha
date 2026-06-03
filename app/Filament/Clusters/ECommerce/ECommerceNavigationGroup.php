<?php

namespace App\Filament\Clusters\ECommerce;

use Filament\Support\Contracts\HasLabel;

enum ECommerceNavigationGroup implements HasLabel
{
    case Transaksi;
    case Katalog;
    case Promosi;
    case Reseller;
    case PengaturanToko;

    public function getLabel(): string
    {
        return match ($this) {
            self::Transaksi => 'Transaksi',
            self::Katalog => 'Katalog',
            self::Promosi => 'Promosi',
            self::Reseller => 'Reseller',
            self::PengaturanToko => 'Pengaturan Toko',
        };
    }
}
