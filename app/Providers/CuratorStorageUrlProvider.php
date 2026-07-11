<?php

namespace App\Providers;

use Awcodes\Curator\Concerns\UrlProvider;
use Illuminate\Support\Facades\Storage;

class CuratorStorageUrlProvider implements UrlProvider
{
    public static function getThumbnailUrl(string $path): string
    {
        return Storage::disk(config('curator.default_disk', 'public'))->url($path);
    }

    public static function getMediumUrl(string $path): string
    {
        return Storage::disk(config('curator.default_disk', 'public'))->url($path);
    }

    public static function getLargeUrl(string $path): string
    {
        return Storage::disk(config('curator.default_disk', 'public'))->url($path);
    }
}
