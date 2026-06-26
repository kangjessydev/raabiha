<?php

namespace App\Filament\Clusters\Dashboard\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use App\Filament\Clusters\Dashboard\DashboardCluster;
use App\Models\Pageview;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoogleAnalytics extends Page
{
    use HasPageShield;

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 2;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';
    
    protected string $view = 'filament.clusters.dashboard.pages.google-analytics';

    public string $period = 'today';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public static function getNavigationLabel(): string
    {
        return 'Analitik Pengunjung';
    }

    public function getTitle(): string
    {
        return 'Analitik Pengunjung';
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    /**
     * Get view data for rendering
     */
    protected function getViewData(): array
    {
        // 1. Dapatkan Query terfilter
        $query = Pageview::query();

        if ($this->period === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($this->period === 'yesterday') {
            $query->whereDate('created_at', now()->subDay()->toDateString());
        } elseif ($this->period === 'this_week') {
            $query->whereBetween('created_at', [now()->startOfWeek()->startOfDay(), now()->endOfWeek()->endOfDay()]);
        } elseif ($this->period === 'this_month') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        } elseif ($this->period === 'custom' && $this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        // Stats utama untuk periode terpilih
        $totalPageviews = (clone $query)->count();
        $uniqueVisitors = (clone $query)->distinct('session_id')->count('session_id');

        // 2. Hitung Pertumbuhan Hari Ini vs Kemarin (Unique Visitors)
        $todayUnique = Pageview::whereDate('created_at', now()->toDateString())->distinct('session_id')->count('session_id');
        $yesterdayUnique = Pageview::whereDate('created_at', now()->subDay()->toDateString())->distinct('session_id')->count('session_id');
        
        $todayGrowth = 0;
        if ($yesterdayUnique > 0) {
            $todayGrowth = (($todayUnique - $yesterdayUnique) / $yesterdayUnique) * 100;
        } elseif ($todayUnique > 0) {
            $todayGrowth = 100; // naik 100% jika kemarin 0 dan hari ini ada
        }

        // 3. Hitung Pertumbuhan Bulan Ini vs Bulan Lalu (Unique Visitors)
        $thisMonthUnique = Pageview::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->distinct('session_id')
            ->count('session_id');
        $lastMonthUnique = Pageview::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->distinct('session_id')
            ->count('session_id');

        $monthGrowth = 0;
        if ($lastMonthUnique > 0) {
            $monthGrowth = (($thisMonthUnique - $lastMonthUnique) / $lastMonthUnique) * 100;
        } elseif ($thisMonthUnique > 0) {
            $monthGrowth = 100;
        }

        // 4. Halaman Populer (Page Type: other, static_page, sales_page, home)
        $topPages = (clone $query)
            ->select('url', 'title', DB::raw('count(*) as views_count'), DB::raw('count(distinct session_id) as visitors_count'))
            ->groupBy('url', 'title')
            ->orderByDesc('views_count')
            ->limit(8)
            ->get();

        // 5. Produk Populer (Page Type: product)
        $topProducts = (clone $query)
            ->where('page_type', 'product')
            ->select('model_id', 'title', DB::raw('count(*) as views_count'), DB::raw('count(distinct session_id) as visitors_count'))
            ->groupBy('model_id', 'title')
            ->orderByDesc('views_count')
            ->limit(8)
            ->get();

        // 6. Artikel Populer (Page Type: post)
        $topPosts = (clone $query)
            ->where('page_type', 'post')
            ->select('model_id', 'title', DB::raw('count(*) as views_count'), DB::raw('count(distinct session_id) as visitors_count'))
            ->groupBy('model_id', 'title')
            ->orderByDesc('views_count')
            ->limit(8)
            ->get();

        return [
            'totalPageviews' => $totalPageviews,
            'uniqueVisitors' => $uniqueVisitors,
            'todayUnique' => $todayUnique,
            'todayGrowth' => $todayGrowth,
            'thisMonthUnique' => $thisMonthUnique,
            'monthGrowth' => $monthGrowth,
            'topPages' => $topPages,
            'topProducts' => $topProducts,
            'topPosts' => $topPosts,
        ];
    }
}
