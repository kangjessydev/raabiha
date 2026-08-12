<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Imports\ProductImporter;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->label('Impor Produk')
                ->importer(ProductImporter::class)
                ->jobConnection('sync'),

            \Filament\Actions\Action::make('export_products')
                ->label('Ekspor Produk')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    \Filament\Schemas\Components\Fieldset::make('Filter Data')
                        ->schema([
                            \Filament\Forms\Components\Select::make('category_id')
                                ->label('Kategori')
                                ->placeholder('Semua Kategori')
                                ->options(\App\Models\Category::pluck('name', 'id'))
                                ->native(false),

                            \Filament\Forms\Components\Select::make('is_active')
                                ->label('Status Produk')
                                ->placeholder('Semua Status')
                                ->options([
                                    '1' => 'Aktif',
                                    '0' => 'Non-Aktif',
                                ])
                                ->native(false),

                            \Filament\Forms\Components\Select::make('channel_visibility')
                                ->label('Channel')
                                ->placeholder('Semua Channel')
                                ->options([
                                    'both'        => 'Semua (Ecommerce & POS)',
                                    'online_only' => 'Ecommerce Only',
                                    'pos_only'    => 'POS Only',
                                ])
                                ->native(false),

                            \Filament\Forms\Components\DatePicker::make('date_from')
                                ->label('Dari Tanggal')
                                ->displayFormat('d/m/Y')
                                ->native(false),

                            \Filament\Forms\Components\DatePicker::make('date_until')
                                ->label('Sampai Tanggal')
                                ->displayFormat('d/m/Y')
                                ->native(false),
                        ])
                        ->columns(2),
                ])
                ->action(function (array $data) {
                    \App\Jobs\ExportProductsCsvJob::dispatch(auth()->user(), $data);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Proses Ekspor Dimulai')
                        ->body('File CSV sedang dibuat di latar belakang. Anda akan menerima notifikasi jika sudah selesai.')
                        ->success()
                        ->send();
                }),

            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\Products\Widgets\ProductStatsWidget::class,
        ];
    }
}
