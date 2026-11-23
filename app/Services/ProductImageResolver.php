<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ProductImageResolver
{
    public const PLACEHOLDER_PATH = 'assets/images/placeholder.webp';

    public static function placeholderUrl(): string
    {
        return asset(self::PLACEHOLDER_PATH);
    }

    public static function mediaToUrl(Media $media): string
    {
        if (filled($media->url)) {
            return (string) $media->url;
        }

        return asset('storage/'.ltrim((string) $media->path, '/'));
    }

    /**
     * Preload Media records in a single batch query for a collection of products
     * (and optionally CartItem/OrderItem objects containing `product` and `variant`).
     *
     * @param  iterable<mixed>  $records
     * @return Collection<int, Media>
     */
    public static function preloadMedia(iterable $records): Collection
    {
        $ids = [];

        foreach ($records as $record) {
            $product = $record instanceof Product ? $record : ($record->product ?? null);
            if ($product instanceof Product && is_array($product->images)) {
                foreach ($product->images as $img) {
                    if (is_numeric($img)) {
                        $ids[] = (int) $img;
                    }
                }
            }

            $variant = $record instanceof ProductVariant ? $record : ($record->variant ?? null);
            if ($variant instanceof ProductVariant && ! empty($variant->media_id) && ! $variant->relationLoaded('media')) {
                $ids[] = (int) $variant->media_id;
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));

        if (empty($ids)) {
            return collect();
        }

        return Media::whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * Resolve a single raw image entry (Curator ID, full URL, or relative path) to a displayable URL.
     *
     * @param  Collection<int, Media>|null  $preloadedMedia
     */
    public static function resolveSingle(int|string|null $item, ?Collection $preloadedMedia = null, ?string $fallback = null): ?string
    {
        if (blank($item)) {
            return $fallback;
        }

        if (is_numeric($item)) {
            $id = (int) $item;
            $media = $preloadedMedia?->get($id) ?? Media::find($id);

            return $media ? self::mediaToUrl($media) : $fallback;
        }

        if (is_string($item)) {
            $trimmed = trim($item);
            if ($trimmed === '') {
                return $fallback;
            }

            if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
                return $trimmed;
            }

            $clean = ltrim($trimmed, '/');

            if (Storage::disk('public')->exists($clean)) {
                return asset('storage/'.$clean);
            }

            if (str_starts_with($clean, 'storage/') || str_starts_with($clean, 'assets/')) {
                return asset($clean);
            }

            if (file_exists(public_path($clean))) {
                return asset($clean);
            }

            return asset('storage/'.$clean);
        }

        return $fallback;
    }

    /**
     * Resolve the primary (first) image URL of a Product, with placeholder.webp fallback.
     *
     * @param  Collection<int, Media>|null  $preloadedMedia
     */
    public static function resolvePrimaryUrl(Product $product, ?Collection $preloadedMedia = null, ?string $fallback = null): string
    {
        $fallbackUrl = $fallback ?? self::placeholderUrl();
        $images = is_array($product->images) ? array_values($product->images) : (filled($product->images) ? [$product->images] : []);

        if (empty($images)) {
            return $fallbackUrl;
        }

        $resolved = self::resolveSingle($images[0], $preloadedMedia, null);

        return $resolved ?? $fallbackUrl;
    }

    /**
     * Resolve image URL for a ProductVariant (prioritizing its own Curator media,
     * falling back to its parent Product primary image, then placeholder.webp).
     *
     * @param  Collection<int, Media>|null  $preloadedMedia
     */
    public static function resolveVariantUrl(ProductVariant $variant, ?Product $fallbackProduct = null, ?Collection $preloadedMedia = null): string
    {
        if (! empty($variant->media_id)) {
            if ($variant->relationLoaded('media') && $variant->media) {
                return self::mediaToUrl($variant->media);
            }

            $variantUrl = self::resolveSingle((int) $variant->media_id, $preloadedMedia, null);
            if ($variantUrl !== null) {
                return $variantUrl;
            }
        }

        $product = $fallbackProduct ?? $variant->product;
        if ($product instanceof Product) {
            return self::resolvePrimaryUrl($product, $preloadedMedia);
        }

        return self::placeholderUrl();
    }

    /**
     * Resolve full product gallery with strict 1-to-1 parallel URLs and Curator media IDs.
     * Supports numeric Curator IDs, full URLs, relative paths, or mixed arrays with zero N+1 queries.
     *
     * @return array{urls: array<int, string>, media_ids: array<int, int|null>}
     */
    public static function resolveGallery(Product $product): array
    {
        $urls = [];
        $mediaIds = [];
        $images = is_array($product->images) ? array_values($product->images) : [];

        if (! empty($images)) {
            $numericIds = [];
            $stringItems = [];

            foreach ($images as $item) {
                if (is_numeric($item)) {
                    $numericIds[] = (int) $item;
                } elseif (is_string($item) && trim($item) !== '') {
                    $stringItems[] = trim($item);
                }
            }

            $mediaById = ! empty($numericIds)
                ? Media::whereIn('id', array_values(array_unique($numericIds)))->get()->keyBy('id')
                : collect();

            $mediaByString = ! empty($stringItems)
                ? self::findMediaMatchingStrings($stringItems)
                : collect();

            foreach ($images as $item) {
                if (is_numeric($item)) {
                    $id = (int) $item;
                    $media = $mediaById->get($id);
                    if ($media) {
                        $urls[] = self::mediaToUrl($media);
                        $mediaIds[] = $id;
                    }
                } elseif (is_string($item) && trim($item) !== '') {
                    $trimmed = trim($item);
                    $url = self::resolveSingle($trimmed, null, null);
                    if ($url !== null) {
                        $matchedMedia = self::matchMediaFromCollection($trimmed, $mediaByString);
                        $urls[] = $url;
                        $mediaIds[] = $matchedMedia ? (int) $matchedMedia->id : null;
                    }
                }
            }
        }

        if (empty($urls)) {
            $urls[] = self::placeholderUrl();
            $mediaIds[] = null;
        }

        return [
            'urls' => $urls,
            'media_ids' => $mediaIds,
        ];
    }

    /**
     * Extract candidate path/name strings from a URL or relative path for matching against `curator`.
     *
     * @return array<int, string>
     */
    public static function extractLookupCandidates(string $raw): array
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return [];
        }

        $pathOnly = parse_url($trimmed, PHP_URL_PATH) ?? $trimmed;
        $clean = ltrim($pathOnly, '/');
        $storageClean = (string) preg_replace('#^storage/#', '', $clean);
        $base = basename($pathOnly);
        $nameWithoutExt = pathinfo($base, PATHINFO_FILENAME);

        return array_values(array_unique(array_filter([
            $clean,
            $storageClean,
            $base,
            $nameWithoutExt,
        ], fn (string $v) => $v !== '')));
    }

    /**
     * Resolve an array of mixed image entries (Curator IDs, URLs, paths) into integer Curator IDs.
     * Unmatched entries are skipped.
     *
     * @param  array<int, mixed>  $items
     * @return array<int, int>
     */
    public static function resolveMediaIds(array $items): array
    {
        $result = self::normalizeImageArray($items);

        return array_values(array_map(
            'intval',
            array_filter($result['normalized'], fn ($v) => is_int($v) || is_numeric($v))
        ));
    }

    /**
     * Normalize an array of product images (IDs, URLs, paths) into Curator integer IDs.
     * Zero Data Loss: unmatched strings are preserved as-is and listed in `unmatched`.
     *
     * @param  array<int, mixed>  $images
     * @return array{normalized: array<int, int|string>, converted_count: int, unmatched: array<int, string>, changed: bool}
     */
    public static function normalizeImageArray(array $images): array
    {
        $numericIds = [];
        $stringItems = [];

        foreach ($images as $item) {
            if (is_numeric($item)) {
                $numericIds[] = (int) $item;
            } elseif (is_string($item) && trim($item) !== '') {
                $stringItems[] = trim($item);
            }
        }

        $existingIds = ! empty($numericIds)
            ? Media::whereIn('id', array_values(array_unique($numericIds)))->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $mediaByString = ! empty($stringItems)
            ? self::findMediaMatchingStrings($stringItems)
            : collect();

        $normalized = [];
        $convertedCount = 0;
        $unmatched = [];
        $changed = false;

        foreach ($images as $item) {
            if (is_numeric($item)) {
                $intId = (int) $item;
                if (in_array($intId, $existingIds, true)) {
                    $normalized[] = $intId;
                    if ($item !== $intId) {
                        $changed = true;
                    }
                } else {
                    // Keep original value if ID not found to avoid silent data loss
                    $normalized[] = $intId;
                }
            } elseif (is_string($item) && trim($item) !== '') {
                $trimmed = trim($item);
                $matched = self::matchMediaFromCollection($trimmed, $mediaByString);
                if ($matched) {
                    $normalized[] = (int) $matched->id;
                    $convertedCount++;
                    $changed = true;
                } else {
                    // Zero data loss: retain unmatched string
                    $normalized[] = $trimmed;
                    $unmatched[] = $trimmed;
                }
            }
        }

        return [
            'normalized' => $normalized,
            'converted_count' => $convertedCount,
            'unmatched' => $unmatched,
            'changed' => $changed,
        ];
    }

    /**
     * Batch-query Media records matching any of the given URL/path strings.
     *
     * @param  array<int, string>  $strings
     * @return Collection<int, Media>
     */
    private static function findMediaMatchingStrings(array $strings): Collection
    {
        $candidates = [];
        foreach ($strings as $str) {
            foreach (self::extractLookupCandidates($str) as $candidate) {
                $candidates[] = $candidate;
            }
        }

        $candidates = array_values(array_unique($candidates));

        if (empty($candidates)) {
            return collect();
        }

        return Media::whereIn('path', $candidates)
            ->orWhereIn('name', $candidates)
            ->get();
    }

    /**
     * Match a single URL/path string against a preloaded Media collection.
     * Prioritizes exact `path` match before falling back to `name` match.
     *
     * @param  Collection<int, Media>  $mediaCollection
     */
    private static function matchMediaFromCollection(string $raw, Collection $mediaCollection): ?Media
    {
        if ($mediaCollection->isEmpty()) {
            return null;
        }

        $candidates = self::extractLookupCandidates($raw);

        $byPath = $mediaCollection->first(fn (Media $m) => in_array($m->path, $candidates, true));
        if ($byPath) {
            return $byPath;
        }

        return $mediaCollection->first(fn (Media $m) => in_array($m->name, $candidates, true));
    }
}
