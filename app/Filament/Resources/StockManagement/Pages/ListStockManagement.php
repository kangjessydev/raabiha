<?php

namespace App\Filament\Resources\StockManagement\Pages;

use App\Filament\Resources\StockManagement\StockManagementResource;
use Filament\Resources\Pages\ListRecords;

class ListStockManagement extends ListRecords
{
    protected static string $resource = StockManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('settings')
                ->label('Pengaturan Stok')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
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
