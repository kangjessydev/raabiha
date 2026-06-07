<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\SiteSetting;

class AnalyticsDashboard extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.pages.analytics-dashboard';

    protected static \UnitEnum|string|null $navigationGroup = 'Dasbor';
    
    protected static ?string $navigationLabel = 'Analitik Pengunjung';

    protected static ?string $title = 'Analitik Pengunjung';

    public ?string $lookerStudioUrl = null;

    public function mount(): void
    {
        $this->lookerStudioUrl = SiteSetting::where('key', 'looker_studio_embed_url')->value('value');
    }
}
