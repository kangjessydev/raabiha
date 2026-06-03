<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ECommerce\ECommerceCluster;

use Filament\Pages\Page;

class TransactionReport extends Page
{
    protected static ?string $cluster = ECommerceCluster::class;
                    
    public static function getNavigationIcon(): ?string { return 'heroicon-o-chart-bar'; }
    public static function getNavigationGroup(): string|\BackedEnum|null { return \App\Filament\Clusters\ECommerce\ECommerceNavigationGroup::Transaksi; }
    public static function getNavigationLabel(): string { return 'Laporan Transaksi'; }
    public function getTitle(): string { return 'Laporan Transaksi'; }

    protected string $view = 'filament.pages.transaction-report';
}
