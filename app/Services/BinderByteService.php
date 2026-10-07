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

        // Circuit breaker check: jika API BinderByte sedang down, jangan tunggu lagi
        if (Cache::get('binderbyte_circuit_tripped')) {
            Log::warning('BinderByte circuit is tripped (open), skipping cost API call.');
            return [];
        }

        // Pastikan origin & destination memiliki format valid dengan prefix 'dist_'
        // Sanitasi: bersihkan prefix 'dist_', hanya izinkan angka dan titik, lalu pasang kembali 'dist_'
        $rawOrigin = preg_replace('/^dist_/', '', trim($origin));
        $rawDestination = preg_replace('/^dist_/', '', trim($destination));

        $sanitizedOrigin = preg_replace('/[^0-9.]/', '', $rawOrigin);
        $sanitizedDestination = preg_replace('/[^0-9.]/', '', $rawDestination);

        if (empty($sanitizedOrigin) || empty($sanitizedDestination)) {
            Log::warning('BinderByte origin or destination is invalid after sanitation', [
                'origin' => $origin,
                'destination' => $destination
            ]);
            return [];
        }

        $formattedOrigin = 'dist_' . $sanitizedOrigin;
        $formattedDestination = 'dist_' . $sanitizedDestination;

        // Cache cost calculation for 6 hours to save API hits
        $cacheKey = sprintf(
            'binderbyte_cost_%s_%s_%d_%s',
            $formattedOrigin,
            $formattedDestination,
            $weight,
            str_replace(',', '_', $couriers)
        );

        $weightInKg = $weight / 1000;

        return Cache::remember($cacheKey, 6 * 60 * 60, function () use ($apiKey, $formattedOrigin, $formattedDestination, $weight, $weightInKg, $couriers) {
            try {
                // Timeout 5 detik agar aman dan tidak memblokir antrean
                $response = Http::timeout(5)
                    ->asForm()
                    ->post('https://api.binderbyte.com/v1/cost', [
                        'api_key' => $apiKey,
                        'origin' => $formattedOrigin,
                        'destination' => $formattedDestination,
                        'weight' => $weightInKg,
                        'courier' => $couriers,
                    ]);

                if ($response->successful() && $response->json('code') == '200') {
                    // Reset penghitung kegagalan jika berhasil
                    Cache::forget('binderbyte_consecutive_failures');
                    return $response->json('data') ?? [];
                }

                // Catat kegagalan beruntun untuk circuit breaker
                $failures = (int) Cache::get('binderbyte_consecutive_failures', 0) + 1;
                Cache::put('binderbyte_consecutive_failures', $failures, 300);
                if ($failures >= 3) {
                    Cache::put('binderbyte_circuit_tripped', true, 180); // Tahan circuit breaker 3 menit
                    Log::warning('BinderByte circuit breaker tripped due to 3 consecutive failures.');
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
            } catch (\Throwable $e) {
                $failures = (int) Cache::get('binderbyte_consecutive_failures', 0) + 1;
                Cache::put('binderbyte_consecutive_failures', $failures, 300);
                if ($failures >= 3) {
                    Cache::put('binderbyte_circuit_tripped', true, 180);
                    Log::warning('BinderByte circuit breaker tripped due to exceptions.');
                }

                Log::error('BinderByte API getShippingCost exception', [
                    'message' => $e->getMessage()
                ]);
                return [];
            }
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
