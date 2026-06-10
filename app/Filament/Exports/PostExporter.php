<?php

namespace App\Filament\Exports;

use App\Models\Post;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class PostExporter extends Exporter
{
    protected static ?string $model = Post::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('title')->label('Judul'),
            ExportColumn::make('slug')->label('Slug'),
            ExportColumn::make('category.name')->label('Kategori'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('published_at')->label('Tanggal Terbit'),
            ExportColumn::make('meta_title')->label('Meta Title'),
            ExportColumn::make('meta_description')->label('Meta Description'),
            ExportColumn::make('created_at')->label('Dibuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor artikel selesai: ' . Number::format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
