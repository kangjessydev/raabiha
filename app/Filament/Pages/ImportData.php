<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use App\Filament\Imports\OrderImporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Imports\UserImporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Imports\PostImporter;
use App\Filament\Imports\VoucherImporter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ImportAction;
use Filament\Pages\Page;

class ImportData extends Page
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan Toko & Sistem';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.import-data';

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
            ActionGroup::make([
                ImportAction::make('import_products')
                    ->label('Impor Produk')
                    ->importer(ProductImporter::class)
                    ->jobConnection('sync')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('primary')
                    ->modalDescription(new \Illuminate\Support\HtmlString('
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                            <p><strong>Panduan Pengisian Excel / CSV:</strong></p>
                            <ul class="list-disc pl-4 space-y-1 mt-1">
                                <li><strong>Produk Utama (Varian? = Tidak):</strong> Wajib isi: Nama Produk, Harga, Stok, Berat.</li>
                                <li><strong>Varian Anak (Varian? = Ya):</strong> Wajib isi: Nama Produk <em>(sesuai nama induk)</em>, Nama Varian, Harga, Stok, Berat.</li>
                                <li><strong>Channel:</strong> Isi kolom <em>Tampil Di (Channel)</em> dengan: <code>both</code>, <code>online_only</code>, atau <code>pos_only</code>.</li>
                            </ul>
                        </div>
                    ')),

                ImportAction::make('import_orders')
                    ->label('Impor Pesanan')
                    ->importer(OrderImporter::class)
                    ->jobConnection('sync')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->modalDescription(''),

                ImportAction::make('import_users')
                    ->label('Impor Pengguna')
                    ->importer(UserImporter::class)
                    ->jobConnection('sync')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->modalDescription(''),

                ImportAction::make('import_categories')
                    ->label('Impor Kategori')
                    ->importer(CategoryImporter::class)
                    ->jobConnection('sync')
                    ->icon('heroicon-o-folder')
                    ->color('warning')
                    ->modalDescription(''),

                ImportAction::make('import_posts')
                    ->label('Impor Artikel Blog')
                    ->importer(PostImporter::class)
                    ->jobConnection('sync')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->modalDescription(''),

                ImportAction::make('import_vouchers')
                    ->label('Impor Voucher')
                    ->importer(VoucherImporter::class)
                    ->jobConnection('sync')
                    ->icon('heroicon-o-ticket')
                    ->color('danger')
                    ->modalDescription(''),
            ])
            ->label('Impor Data')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->button(),
        ];
    }
}
