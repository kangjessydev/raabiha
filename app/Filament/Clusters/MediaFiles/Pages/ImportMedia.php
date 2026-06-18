<?php

namespace App\Filament\Clusters\MediaFiles\Pages;

use App\Filament\Clusters\MediaFiles;
use App\Filament\Imports\OrderImporter;
use App\Filament\Imports\ProductImporter;
use BackedEnum;
use Filament\Actions\ImportAction;
use Filament\Pages\Page;

class ImportMedia extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected string $view = 'filament.clusters.media-files.pages.import-media';

    protected static ?string $cluster = MediaFiles::class;

    public static function getNavigationLabel(): string
    {
        return 'Impor Data';
    }

    public function getTitle(): string
    {
        return 'Impor Data';
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make('import_products')
                ->label('Impor Produk')
                ->importer(ProductImporter::class)
                ->icon('heroicon-o-shopping-bag')
                ->color('primary')
                ->authorize('viewAny')
                ->modalDescription(new \Illuminate\Support\HtmlString('
                    <div class="prose text-sm text-gray-600 dark:text-gray-400 mt-2">
                        <p><strong>Panduan Pengisian Excel:</strong></p>
                        <ul class="list-disc pl-4 space-y-1 mt-1">
                            <li><strong>Produk Utama (Varian? = Tidak):</strong><br> Wajib isi: Nama Produk, Harga, Stok, Berat.</li>
                            <li><strong>Varian Anak (Varian? = Ya):</strong><br> Wajib isi: Nama Produk <i>(sesuai nama induk)</i>, Nama Varian, Harga, Stok, Berat.</li>
                        </ul>
                    </div>
                ')),

            ImportAction::make('import_orders')
                ->label('Impor Pesanan')
                ->importer(OrderImporter::class)
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->authorize('viewAny')
                ->modalDescription(''),

            ImportAction::make('import_users')
                ->label('Impor Pengguna')
                ->importer(\App\Filament\Imports\UserImporter::class)
                ->icon('heroicon-o-users')
                ->color('info')
                ->authorize('viewAny')
                ->modalDescription(''),

            ImportAction::make('import_categories')
                ->label('Impor Kategori')
                ->importer(\App\Filament\Imports\CategoryImporter::class)
                ->icon('heroicon-o-folder')
                ->color('warning')
                ->authorize('viewAny')
                ->hidden()
                ->modalDescription(''),

            ImportAction::make('import_posts')
                ->label('Impor Artikel Blog')
                ->importer(\App\Filament\Imports\PostImporter::class)
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->authorize('viewAny')
                ->modalDescription(''),

            ImportAction::make('import_vouchers')
                ->label('Impor Voucher')
                ->importer(\App\Filament\Imports\VoucherImporter::class)
                ->icon('heroicon-o-ticket')
                ->color('danger')
                ->authorize('viewAny')
                ->hidden()
                ->modalDescription(''),
        ];
    }
}
