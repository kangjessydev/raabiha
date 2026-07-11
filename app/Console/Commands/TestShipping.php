<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShippingMethod;
use App\Services\BinderByteService;

class TestShipping extends Command
{
    protected $signature = 'app:test-shipping';
    protected $description = 'Automate testing of shipping rate calculations and cargo filtering across various regions in Indonesia';

    public function handle()
    {
        $this->info("=== MEMULAI PENGUJIAN OTOMATIS TARIF & FILTER PENGIRIMAN ===");
        
        $originLabel = 'Bojongmanggu, Pameungpeuk, KAB. BANDUNG, JAWA BARAT';
        $couriers = 'jne,pos,sicepat,wahana,ninja,anteraja';
        
        $destinations = [
            [
                'name' => 'Kecamatan Terdekat (Baleendah)',
                'id' => '32.04.32',
                'label' => 'BALEENDAH, KAB. BANDUNG, JAWA BARAT, 40375'
            ],
            [
                'name' => 'Kecamatan Terjauh 1 Kabupaten (Rancabali)',
                'id' => '32.04.42',
                'label' => 'RANCABALI, KAB. BANDUNG, JAWA BARAT, 40973'
            ],
            [
                'name' => 'Kecamatan Luar Kabupaten (Kebon Jeruk)',
                'id' => '31.73.05',
                'label' => 'KEBON JERUK, KOTA JAKARTA BARAT, DKI JAKARTA, 11530'
            ],
            [
                'name' => 'Luar Pulau (Parepare, Sulawesi)',
                'id' => '73.72.01',
                'label' => 'SOREANG, KOTA PAREPARE, SULAWESI SELATAN, 91132'
            ],
            [
                'name' => 'Luar Pulau Terjauh (Merauke, Papua)',
                'id' => '93.01.01',
                'label' => 'MERAUKE, KAB. MERAUKE, PAPUA SELATAN, 99611'
            ],
        ];

        $weights = [
            1000 => 'Ringan (1 kg) - Kargo Harus Tersembunyi',
            12000 => 'Berat (12 kg) - Kargo Harus Muncul'
        ];

        $activeCouriers = ShippingMethod::where('is_active', true)->get();
        if ($activeCouriers->isEmpty()) {
            $this->error("Tidak ada kurir yang aktif di database!");
            return 1;
        }
        
        $this->line("Kurir aktif di database: " . $activeCouriers->pluck('name')->implode(', '));

        $roundingMethod = \App\Models\SiteSetting::where('key', 'weight_rounding_method')->value('value') ?? 'ceiling';
        $toleranceGrams = (int) (\App\Models\SiteSetting::where('key', 'weight_tolerance_grams')->value('value') ?? 300);

        foreach ($destinations as $dest) {
            $this->line("\n------------------------------------------------------------");
            $this->info("TUJUAN: {$dest['name']} ({$dest['label']})");
            $this->line("------------------------------------------------------------");

            foreach ($weights as $weight => $weightDesc) {
                // Apply dynamic rounding policy to weight
                $testWeight = $weight;
                if ($roundingMethod === 'ceiling') {
                    $roundedWeight = max(1000, (int) ceil($testWeight / 1000) * 1000);
                } elseif ($roundingMethod === 'half_kg' || $roundingMethod === 'half_kg_ceil') {
                    $roundedWeight = max(1000, (int) ceil($testWeight / 500) * 500);
                } elseif ($roundingMethod === 'half_kg_floor') {
                    $roundedWeight = max(1000, (int) floor($testWeight / 500) * 500);
                } elseif ($roundingMethod === 'half_kg_nearest') {
                    $roundedWeight = max(1000, (int) round($testWeight / 500) * 500);
                } else {
                    $kg = floor($testWeight / 1000);
                    $remainder = $testWeight % 1000;
                    if ($remainder > $toleranceGrams) {
                        $roundedWeight = ($kg + 1) * 1000;
                    } else {
                        $roundedWeight = max(1, $kg) * 1000;
                    }
                }

                $this->warn("Uji Berat Asli: {$testWeight}g (Dibulatkan: {$roundedWeight}g) - {$weightDesc}");

                try {
                    $results = BinderByteService::getShippingCost('32.04.14', $dest['id'], $roundedWeight, $couriers);
                    
                    if (empty($results) || !isset($results['results'])) {
                        $this->error("  API BinderByte tidak mengembalikan hasil (mungkin limit atau timeout).");
                        continue;
                    }

                    $tableRows = [];

                    foreach ($results['results'] as $courierResult) {
                        $courierCode = $courierResult['code'] ?? '';
                        $courierModel = $activeCouriers->firstWhere('code', $courierCode);
                        if (!$courierModel) continue;

                        $courierConfig = $courierModel->config ?? [];
                        $allowedServices = [];
                        
                        if (!empty($courierConfig['allowed_services'])) {
                            if (is_array($courierConfig['allowed_services'])) {
                                $allowedServices = array_map('strtoupper', array_map('trim', $courierConfig['allowed_services']));
                            } else {
                                $allowedServices = array_map('trim', explode(',', strtoupper($courierConfig['allowed_services'])));
                            }
                        }

                        foreach ($courierResult['costs'] ?? [] as $cost) {
                            $rawServiceName = strtoupper($cost['service'] ?? '');
                            $serviceName = $rawServiceName;

                            if (!empty($courierConfig['service_aliases']) && is_array($courierConfig['service_aliases'])) {
                                foreach ($courierConfig['service_aliases'] as $rawCode => $alias) {
                                    if (strtoupper($rawCode) === $rawServiceName) {
                                        $serviceName = $alias;
                                        break;
                                    }
                                }
                            }

                            // Filter using allowed services
                            if (!empty($allowedServices) && !in_array($rawServiceName, $allowedServices)) {
                                continue;
                            }

                            // Dynamic shipping rule verification
                            $shouldShow = $courierModel->shouldShowService($rawServiceName, $roundedWeight, '32.04.14', $dest['label']);

                            // Calculate price
                            $rawPrice = (int)($cost['price'] ?? 0);
                            $price = $rawPrice >= 1000000 ? $rawPrice / 1000 : $rawPrice;

                            // Determine category
                            $cargoKeywords = ['JTR', 'GOKIL', 'CARGO', 'KARGO', 'TRC', 'BIGPACK', 'TRUCK'];
                            $ecoKeywords = ['HALU', 'ECO', 'EKONOMIS', 'OKE', 'LITE', 'ECOREG'];
                            $expressKeywords = ['YES', 'BEST', 'SPS', 'CTCSPS', 'CTCYES', 'NEXTDAY', 'EXPRESS', 'UDRONS', 'ONS'];

                            $isCargo = false;
                            foreach ($cargoKeywords as $kw) {
                                if (str_contains(strtoupper($serviceName), $kw) || str_contains($rawServiceName, $kw)) {
                                    $isCargo = true;
                                    break;
                                }
                            }

                            $isEco = false;
                            foreach ($ecoKeywords as $kw) {
                                if (str_contains(strtoupper($serviceName), $kw) || str_contains($rawServiceName, $kw)) {
                                    $isEco = true;
                                    break;
                                }
                            }

                            $isExpress = false;
                            foreach ($expressKeywords as $kw) {
                                if (str_contains(strtoupper($serviceName), $kw) || str_contains($rawServiceName, $kw)) {
                                    $isExpress = true;
                                    break;
                                }
                            }

                            if ($isCargo) {
                                $category = 'kargo';
                            } elseif ($isExpress) {
                                $category = 'express';
                            } elseif ($isEco) {
                                $category = 'hemat';
                            } else {
                                $category = 'reguler';
                            }

                            $tableRows[] = [
                                'kurir' => $courierModel->name,
                                'layanan' => $serviceName,
                                'raw_price' => $rawPrice,
                                'parsed_price' => 'Rp ' . number_format($price, 0, ',', '.'),
                                'kategori' => $category,
                                'status' => $shouldShow ? 'TAMPIL' : 'SEMBUNYI',
                            ];
                        }
                    }

                    if (empty($tableRows)) {
                        $this->line("  Tidak ada layanan kurir yang lolos penyaringan.");
                    } else {
                        $this->table(
                            ['Kurir', 'Layanan', 'Raw Price', 'Harga Tampil', 'Kategori', 'Status Filter'],
                            $tableRows
                        );
                    }

                } catch (\Exception $e) {
                    $this->error("  Error saat memproses: " . $e->getMessage());
                }
            }
        }

        $this->info("\n=== PENGUJIAN SELESAI ===");
        return 0;
    }
}
