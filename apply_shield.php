<?php

$pages = [
    'app/Filament/Pages/LaporanBisnis.php',
    'app/Filament/Pages/TopbarAnnouncement.php',
    'app/Filament/Pages/Resellers/ResellerSettings.php',
    'app/Filament/Clusters/Marketing/Pages/MainPageSettings.php',
    'app/Filament/Clusters/Dashboard/Pages/GoogleAnalytics.php',
    'app/Filament/Clusters/Settings/Pages/IntegrationSettings.php',
    'app/Filament/Clusters/Settings/Pages/AppearanceSettings.php',
    'app/Filament/Clusters/Settings/Pages/TransactionSettings.php',
    'app/Filament/Clusters/Settings/Pages/GlobalSettings.php',
    'app/Filament/Clusters/MediaFiles/Pages/ExportMedia.php',
    'app/Filament/Clusters/MediaFiles/Pages/ImportMedia.php',
];

foreach ($pages as $page) {
    if (!file_exists($page)) continue;
    $content = file_get_contents($page);
    
    // Check if already applied
    if (strpos($content, 'HasPageShield') !== false) continue;
    
    // Add use BezhanSalleh\FilamentShield\Traits\HasPageShield; after namespace declaration
    $content = preg_replace(
        '/(namespace .*?;)/',
        "$1\n\nuse BezhanSalleh\FilamentShield\\Traits\HasPageShield;",
        $content,
        1
    );
    
    // Add use HasPageShield; inside the class
    $content = preg_replace(
        '/(class [a-zA-Z0-9_]+ extends [a-zA-Z0-9_]+( implements [a-zA-Z0-9_, ]+)?\s*\{)/',
        "$1\n    use HasPageShield;\n",
        $content,
        1
    );
    
    file_put_contents($page, $content);
    echo "Added HasPageShield to $page\n";
}

// Add canViewAny to StockManagementResource
$stockFile = 'app/Filament/Resources/StockManagement/StockManagementResource.php';
$stockContent = file_get_contents($stockFile);
if (strpos($stockContent, 'canViewAny') === false) {
    $canViewAny = "\n    public static function canViewAny(): bool\n    {\n        return auth()->user()->hasAnyRole(['super_admin', 'owner', 'marketing', 'logistics']);\n    }\n";
    $stockContent = preg_replace('/(protected static \?string \$modelLabel = .*?;)/', "$1$canViewAny", $stockContent);
    file_put_contents($stockFile, $stockContent);
    echo "Added canViewAny to StockManagementResource\n";
}
