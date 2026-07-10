<?php

namespace App\Filament\Resources\StockManagement\Pages;

use App\Filament\Resources\StockManagement\StockManagementResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListStockManagement extends ListRecords
{
    protected static string $resource = StockManagementResource::class;

    public function getTabs(): array
    {
        $defaultMin = (int) (\App\Models\SiteSetting::where('key', 'default_minimum_stock')->value('value') ?? 5);

        return [
            'all' => Tab::make('Semua')
                ->badge($this->getModel()::count()),
            'out_of_stock' => Tab::make('Stok Habis')
                ->modifyQueryUsing(fn ($query) => $query->where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('has_variants', false)->where('stock', '<=', 0);
                    })->orWhere(function ($sq) {
                        $sq->where('has_variants', true)->whereHas('variants', fn($vq) => $vq->where('stock', '<=', 0));
                    });
                }))
                ->badge($this->getModel()::where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('has_variants', false)->where('stock', '<=', 0);
                    })->orWhere(function ($sq) {
                        $sq->where('has_variants', true)->whereHas('variants', fn($vq) => $vq->where('stock', '<=', 0));
                    });
                })->count())
                ->badgeColor('danger'),
            'low_stock' => Tab::make('Stok Menipis')
                ->modifyQueryUsing(fn ($query) => $query->where(function ($q) use ($defaultMin) {
                    $q->where(function ($sq) use ($defaultMin) {
                        $sq->where('has_variants', false)
                            ->where('stock', '>', 0)
                            ->whereRaw('stock <= COALESCE(minimum_stock, ?)', [$defaultMin]);
                    })->orWhere(function ($sq) use ($defaultMin) {
                        $sq->where('has_variants', true)
                            ->whereHas('variants', fn($vq) => $vq->where('stock', '>', 0)->whereRaw('stock <= COALESCE(minimum_stock, ?)', [$defaultMin]));
                    });
                }))
                ->badge($this->getModel()::where(function ($q) use ($defaultMin) {
                    $q->where(function ($sq) use ($defaultMin) {
                        $sq->where('has_variants', false)
                            ->where('stock', '>', 0)
                            ->whereRaw('stock <= COALESCE(minimum_stock, ?)', [$defaultMin]);
                    })->orWhere(function ($sq) use ($defaultMin) {
                        $sq->where('has_variants', true)
                            ->whereHas('variants', fn($vq) => $vq->where('stock', '>', 0)->whereRaw('stock <= COALESCE(minimum_stock, ?)', [$defaultMin]));
                    });
                })->count())
                ->badgeColor('warning'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('settings')
                ->label('Pengaturan Stok')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->visible(fn () => auth()->user()->can('Update:Product'))
                ->modalHeading('Pengaturan Stok & Inventaris')
                ->modalWidth('md')
                ->form([
                    \Filament\Forms\Components\TextInput::make('default_minimum_stock')
                        ->label('Batas Stok Minimum (Global)')
                        ->numeric()
                        ->required()
                        ->helperText('Batas stok minimum untuk seluruh produk sebagai acuan notifikasi peringatan. Jika produk memiliki stok minimum khusus, nilai ini akan di-override.')
                ])
                ->fillForm(function (): array {
                    return [
                        'default_minimum_stock' => \App\Models\SiteSetting::where('key', 'default_minimum_stock')->value('value') ?? 5,
                    ];
                })
                ->action(function (array $data) {
                    \App\Models\SiteSetting::updateOrCreate(
                        ['key' => 'default_minimum_stock'],
                        ['value' => $data['default_minimum_stock']]
                    );
                    \Filament\Notifications\Notification::make()
                        ->title('Pengaturan stok berhasil disimpan')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\StockManagement\Widgets\StockLogWidget::class,
        ];
    }
}
