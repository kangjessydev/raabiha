<?php

namespace App\Filament\Clusters\Dashboard\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;

use BezhanSalleh\GoogleAnalytics\Pages\GoogleAnalyticsDashboard as BaseDashboard;
use App\Filament\Clusters\Dashboard\DashboardCluster;

class GoogleAnalytics extends BaseDashboard
{
    use HasPageShield;

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 2;
    
    // Opsional: kita bisa men-override navigation label jika diinginkan
    public static function getNavigationLabel(): string
    {
        return 'Analitik Pengunjung';
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Analitik Pengunjung';
    }

    public static function canView(): bool
    {
        return true;
    }

    public function mount(): void
    {
        if (empty(config('analytics.property_id'))) {
            \Filament\Notifications\Notification::make()
                ->title('Google Analytics Belum Dikonfigurasi')
                ->body('Silakan tambahkan ANALYTICS_PROPERTY_ID di file .env dan letakkan file kredensial JSON Anda agar grafik dapat muncul.')
                ->warning()
                ->send();
        }
    }

    protected function getHeaderWidgets(): array
    {
        if (empty(config('analytics.property_id'))) {
            return [];
        }

        return parent::getHeaderWidgets();
    }
}
