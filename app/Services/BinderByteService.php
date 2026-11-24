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

    public static function searchLocations(string $keyword): array
    {
        $apiKey = self::getApiKey();
        $keyword = trim($keyword);
        if (! $apiKey || mb_strlen($keyword) < 2) {
            return [];
        }

        $cacheKey = 'binderbyte_locations_v1_' . md5(mb_strtolower($keyword));
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ! empty($cached)) {
            return $cached;
        }

        try {
            $response = Http::timeout(6)->get('https://api.binderbyte.com/v1/locations', [
                'api_key' => $apiKey,
                'search' => $keyword,
            ]);

            if ($response->successful() && $response->json('code') == '200') {
                $data = $response->json('data') ?? [];
                if (is_array($data) && ! empty($data)) {
                    Cache::put($cacheKey, $data, now()->addDays(7));
                    return $data;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('BinderByte API searchLocations exception', [
                'keyword' => $keyword,
                'message' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Terjemahkan kode wilayah Kemendagri (misal "32.04.37") + label ("Soreang, KAB. BANDUNG, JAWA BARAT")
     * menjadi ID lokasi resmi BinderByte /v1/locations (misal "village_4975").
     */
    public static function resolveLocationId(string $idOrCode, ?string $label = null): ?string
    {
        $trimmed = trim($idOrCode);
        if ($trimmed === '') {
            return null;
        }

        // Jika sudah berformat ID resmi BinderByte (/v1/locations), langsung pakai
        if (preg_match('/^(village|subdistrict|district|city)_\d+$/i', $trimmed)) {
            return strtolower($trimmed);
        }

        $labelStr = trim((string) $label);
        if ($labelStr === '') {
            return null;
        }

        $cacheKey = 'binderbyte_resolved_loc_v1_' . md5($trimmed . '|' . mb_strtolower($labelStr));
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        // Normalisasi potongan label: "Kecamatan, KAB. KOTA, PROVINSI"
        $rawParts = array_values(array_filter(array_map('trim', explode(',', $labelStr))));
        if (empty($rawParts)) {
            return null;
        }

        $cleanToken = function (string $text): string {
            $text = preg_replace('/(^|\s)(kab\.?|kabupaten|kota|adm\.?|administrasi|kec\.?|kecamatan|kel\.?|kelurahan|desa)(?=\s|$)/iu', ' ', $text);
            $text = preg_replace('/\s+/', ' ', (string) $text);
            return mb_strtolower(trim($text));
        };

        $tokens = array_values(array_filter(array_map($cleanToken, $rawParts)));
        $primaryKeyword = $tokens[0] ?? '';
        if ($primaryKeyword === '') {
            return null;
        }

        $candidates = self::searchLocations($primaryKeyword);
        if (empty($candidates) && isset($tokens[1]) && $tokens[1] !== '') {
            $candidates = self::searchLocations($tokens[1]);
        }

        if (empty($candidates)) {
            return null;
        }

        $bestId = null;
        $bestScore = -1;

        foreach ($candidates as $candidate) {
            $candId = (string) ($candidate['id'] ?? '');
            $candLabel = mb_strtolower((string) ($candidate['label'] ?? ''));
            if ($candId === '' || $candLabel === '') {
                continue;
            }

            $score = 0;
            foreach ($tokens as $idx => $token) {
                if ($token !== '' && str_contains($candLabel, $token)) {
                    // Bobot lebih tinggi untuk kecocokan kecamatan & kota/kabupaten
                    $score += ($idx === 0 ? 4 : ($idx === 1 ? 3 : 2));
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestId = $candId;
            }
        }

        if ($bestId) {
            Cache::put($cacheKey, $bestId, now()->addDays(30));
        }

        return $bestId;
    }

    public static function getShippingCost(
        string $origin,
        string $destination,
        int $weight,
        string $couriers,
        ?string $originLabel = null,
        ?string $destinationLabel = null
    ): array {
        $apiKey = self::getApiKey();
        if (!$apiKey) {
            return [];
        }

        // Circuit breaker check: jika API BinderByte sedang down, jangan tunggu lagi
        if (Cache::get('binderbyte_circuit_tripped')) {
            Log::warning('BinderByte circuit is tripped (open), skipping cost API call.');
            return [];
        }

        $formattedOrigin = self::resolveLocationId($origin, $originLabel);
        $formattedDestination = self::resolveLocationId($destination, $destinationLabel);

        if (empty($formattedOrigin) || empty($formattedDestination)) {
            Log::warning('BinderByte origin or destination could not be resolved to valid location IDs', [
                'origin' => $origin,
                'origin_label' => $originLabel,
                'destination' => $destination,
                'destination_label' => $destinationLabel,
            ]);
            return [];
        }

        // Cache cost calculation for 6 hours (HANYA untuk hasil sukses, tidak men-cache array kosong)
        $cacheKey = sprintf(
            'binderbyte_cost_v2_%s_%s_%d_%s',
            $formattedOrigin,
            $formattedDestination,
            $weight,
            str_replace(',', '_', $couriers)
        );

        $cachedResult = Cache::get($cacheKey);
        if (is_array($cachedResult) && ! empty($cachedResult)) {
            return $cachedResult;
        }

        $weightInKg = $weight / 1000;

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
                $data = $response->json('data') ?? [];
                if (is_array($data) && ! empty($data)) {
                    Cache::put($cacheKey, $data, 6 * 60 * 60);
                }
                return is_array($data) ? $data : [];
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
