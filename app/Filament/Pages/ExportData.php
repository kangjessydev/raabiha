<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use App\Filament\Exports\CategoryExporter;
use App\Filament\Exports\OrderExporter;
use App\Filament\Exports\PostExporter;
use App\Filament\Exports\ProductExporter;
use App\Filament\Exports\UserExporter;
use App\Filament\Exports\VoucherExporter;
use Filament\Actions\ExportAction;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class ExportData extends Page
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static \UnitEnum|string|null $navigationGroup = 'Katalog & Stok Barang';

    protected static ?int $navigationSort = 91;

    protected string $view = 'filament.pages.export-data';

    public static function getNavigationLabel(): string
    {
        return 'Ekspor Data';
    }

    public function getTitle(): string
    {
        return 'Ekspor Data';
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make('export_products')
                ->label('Ekspor Produk')
                ->exporter(ProductExporter::class)
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),

            ExportAction::make('export_orders')
                ->label('Ekspor Pesanan')
                ->exporter(OrderExporter::class)
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->modifyQueryUsing(fn (Builder $query, array $options) => $query
                    ->when(filled($options['status'] ?? null), fn ($q) => $q->where('status', $options['status']))
                    ->when(filled($options['payment_status'] ?? null), fn ($q) => $q->where('payment_status', $options['payment_status']))
                    ->when(filled($options['date_from'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $options['date_from']))
                    ->when(filled($options['date_until'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $options['date_until']))
                ),

            ExportAction::make('export_users')
                ->label('Ekspor Pengguna')
                ->exporter(UserExporter::class)
                ->icon('heroicon-o-users')
                ->color('info')
                ->modifyQueryUsing(fn (Builder $query, array $options) => $query
                    ->when(filled($options['reseller_status'] ?? null), fn ($q) => $options['reseller_status'] === 'none'
                        ? $q->where('is_reseller', false)
                        : $q->where('is_reseller', true)->where('reseller_status', $options['reseller_status'])
                    )
                    ->when(filled($options['date_from'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $options['date_from']))
                    ->when(filled($options['date_until'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $options['date_until']))
                ),

            ExportAction::make('export_categories')
                ->label('Ekspor Kategori')
                ->exporter(CategoryExporter::class)
                ->icon('heroicon-o-folder')
                ->color('warning'),

            ExportAction::make('export_posts')
                ->label('Ekspor Artikel Blog')
                ->exporter(PostExporter::class)
                ->icon('heroicon-o-document-text')
                ->color('gray'),

            ExportAction::make('export_vouchers')
                ->label('Ekspor Voucher')
                ->exporter(VoucherExporter::class)
                ->icon('heroicon-o-ticket')
                ->color('danger'),
        ];
    }
}
