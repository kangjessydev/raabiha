<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class EmsifaService
{
    protected static $baseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/api';

    public static function getProvinces()
    {
        return Cache::remember('emsifa_provinces', 86400, function () {
            try {
                $response = Http::get(self::$baseUrl . '/provinces.json');
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                // Log or handle error if needed
            }
            return [];
        });
    }

    public static function getCities($provinceId)
    {
        if (empty($provinceId)) return [];
        
        return Cache::remember("emsifa_cities_{$provinceId}", 86400, function () use ($provinceId) {
            try {
                $response = Http::get(self::$baseUrl . "/regencies/{$provinceId}.json");
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                // Log or handle error
            }
            return [];
        });
    }

    public static function getDistricts($cityId)
    {
        if (empty($cityId)) return [];
        
        return Cache::remember("emsifa_districts_{$cityId}", 86400, function () use ($cityId) {
            try {
                $response = Http::get(self::$baseUrl . "/districts/{$cityId}.json");
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                // Log or handle error
            }
            return [];
        });
    }

    public static function getVillages($districtId)
    {
        if (empty($districtId)) return [];
        
        return Cache::remember("emsifa_villages_{$districtId}", 86400, function () use ($districtId) {
            try {
                $response = Http::get(self::$baseUrl . "/villages/{$districtId}.json");
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                // Log or handle error
            }
            return [];
        });
    }
}
