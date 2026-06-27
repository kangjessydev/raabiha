<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BinderByteService
{
    protected static function getApiKey(): ?string
    {
        return SiteSetting::where('key', 'binderbyte_api_key')->value('value');
    }

    public static function getProvinces(): array
    {
        $apiKey = self::getApiKey();
        if (!$apiKey) {
            Log::warning('BinderByte API Key is not configured.');
            return [];
        }

        return Cache::remember('binderbyte_provinces', 30 * 24 * 60 * 60, function () use ($apiKey) {
            $response = Http::get('https://api.binderbyte.com/wilayah/provinsi', [
                'api_key' => $apiKey
            ]);

            if ($response->successful() && $response->json('code') == '200') {
                return $response->json('value') ?? [];
            }

            Log::error('BinderByte API getProvinces failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return [];
        });
    }

    public static function getCities(string $provinceId): array
    {
        $apiKey = self::getApiKey();
        if (!$apiKey) {
            return [];
        }

        $cacheKey = 'binderbyte_cities_' . $provinceId;
        return Cache::remember($cacheKey, 30 * 24 * 60 * 60, function () use ($apiKey, $provinceId) {
            $response = Http::get('https://api.binderbyte.com/wilayah/kabupaten', [
                'api_key' => $apiKey,
                'id_provinsi' => $provinceId
            ]);

            if ($response->successful()) {
                return $response->json('value') ?? [];
            }

            Log::error('BinderByte API getCities failed', [
                'province_id' => $provinceId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return [];
        });
    }

    public static function getDistricts(string $cityId): array
    {
        $apiKey = self::getApiKey();
        if (!$apiKey) {
            return [];
        }

        $cacheKey = 'binderbyte_districts_' . $cityId;
        return Cache::remember($cacheKey, 30 * 24 * 60 * 60, function () use ($apiKey, $cityId) {
            $response = Http::get('https://api.binderbyte.com/wilayah/kecamatan', [
                'api_key' => $apiKey,
                'id_kabupaten' => $cityId
            ]);

            if ($response->successful()) {
                return $response->json('value') ?? [];
            }

            Log::error('BinderByte API getDistricts failed', [
                'city_id' => $cityId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return [];
        });
    }

    public static function getShippingCost(string $origin, string $destination, int $weight, string $couriers): array
    {
        $apiKey = self::getApiKey();
        if (!$apiKey) {
            return [];
        }

        // Cache cost calculation for 6 hours to save API hits
        $cacheKey = sprintf(
            'binderbyte_cost_%s_%s_%d_%s',
            $origin,
            $destination,
            $weight,
            str_replace(',', '_', $couriers)
        );

        return Cache::remember($cacheKey, 6 * 60 * 60, function () use ($apiKey, $origin, $destination, $weight, $couriers) {
            // Ensure proper format for origin & destination (must have 'dist_' prefix)
            $formattedOrigin = str_starts_with($origin, 'dist_') ? $origin : 'dist_' . $origin;
            $formattedDestination = str_starts_with($destination, 'dist_') ? $destination : 'dist_' . $destination;

            $response = Http::asForm()->post('https://api.binderbyte.com/v1/cost', [
                'api_key' => $apiKey,
                'origin' => $formattedOrigin,
                'destination' => $formattedDestination,
                'weight' => $weight,
                'courier' => $couriers,
            ]);

            if ($response->successful() && $response->json('code') == '200') {
                return $response->json('data') ?? [];
            }

            Log::error('BinderByte API getShippingCost failed', [
                'origin' => $formattedOrigin,
                'destination' => $formattedDestination,
                'weight' => $weight,
                'couriers' => $couriers,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return [];
        });
    }

    public static function trackPackage(string $courier, string $awb): array
    {
        $apiKey = self::getApiKey();
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'BinderByte API Key is not configured.'
            ];
        }

        $response = Http::get('https://api.binderbyte.com/v1/track', [
            'api_key' => $apiKey,
            'courier' => strtolower($courier),
            'awb' => $awb
        ]);

        if ($response->successful() && $response->json('status') == 200) {
            return [
                'success' => true,
                'data' => $response->json('data')
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('message') ?? 'Gagal melacak paket.'
        ];
    }
}
