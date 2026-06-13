<?php

declare(strict_types=1);

return [
    'curation_formats' => Awcodes\Curator\Enums\PreviewableExtensions::toArray(),
    'default_disk' => env('CURATOR_DEFAULT_DISK', 'public'),
    'default_directory' => null,
    'default_visibility' => 'public',
    'features' => [
        'curations' => true,
        'file_swap' => true,
        'directory_restriction' => false,
        'preserve_file_names' => false,
        'tenancy' => [
            'enabled' => false,
            'relationship_name' => null,
        ],
    ],
    'glide_token' => env('CURATOR_GLIDE_TOKEN'),
    'model' => \App\Models\Media::class,
    'path_generator' => null,
    'resource' => [
        'label' => 'Media',
        'plural_label' => 'Media',
        'default_layout' => 'grid',
        'navigation' => [
            'group' => null,
            'icon' => 'heroicon-o-photo',
            'sort' => null,
            'should_register' => true,
            'should_show_badge' => false,
        ],
        'resource' => \App\Filament\Resources\MediaResource::class,
        'pages' => [
            'create' => \App\Filament\Resources\MediaResource\Pages\CreateMedia::class,
            'edit' => \App\Filament\Resources\MediaResource\Pages\EditMedia::class,
            'index' => \App\Filament\Resources\MediaResource\Pages\ListMedia::class,
        ],
        'schemas' => [
            'form' => Awcodes\Curator\Resources\Media\Schemas\MediaForm::class,
        ],
        'tables' => [
            'table' => Awcodes\Curator\Resources\Media\Tables\MediaTable::class,
        ],
    ],
    'url_provider' => Awcodes\Curator\Providers\GlideUrlProvider::class,
];
