<?php

namespace App\Filament\Imports;

use App\Models\Post;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class PostImporter extends Importer
{
    protected static ?string $model = Post::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('post_category_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('user_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('title')
                ->label('Judul')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('slug')
                ->label('Slug')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('content'),
            ImportColumn::make('image')
                ->rules(['max:255']),
            ImportColumn::make('is_published')
                ->boolean()
                ->rules(['boolean', 'nullable']),
            ImportColumn::make('meta_title')
                ->label('Meta Title')
                ->rules(['max:255']),
            ImportColumn::make('meta_description')
                ->label('Meta Description'),
            ImportColumn::make('focus_keyword')
                ->rules(['max:255']),
            ImportColumn::make('published_at')
                ->label('Tanggal Terbit')
                ->rules(['datetime']),
        ];
    }

    public function resolveRecord(): Post
    {
        $slug = filled($this->data['slug'] ?? null) 
            ? $this->data['slug'] 
            : \Illuminate\Support\Str::slug($this->data['title'] ?? 'post');

        return Post::firstOrNew([
            'slug' => $slug,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your post import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
