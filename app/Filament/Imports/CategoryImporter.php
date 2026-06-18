<?php

namespace App\Filament\Imports;

use App\Models\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class CategoryImporter extends Importer
{
    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nama Kategori')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('slug')
                ->label('Slug')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('description')
                ->label('Deskripsi'),
            ImportColumn::make('image')
                ->label('Gambar')
                ->rules(['max:255']),
            ImportColumn::make('is_active')
                ->label('Aktif?')
                ->boolean()
                ->rules(['boolean', 'nullable']),
        ];
    }

    public function resolveRecord(): Category
    {
        $slug = filled($this->data['slug'] ?? null) 
            ? $this->data['slug'] 
            : \Illuminate\Support\Str::slug($this->data['name'] ?? 'kategori');

        return Category::firstOrNew([
            'slug' => $slug,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your category import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
