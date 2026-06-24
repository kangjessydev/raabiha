<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'logo',
        'config',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Menentukan apakah suatu layanan pengiriman boleh ditampilkan berdasarkan berat, asal, dan tujuan.
     */
    public function shouldShowService(string $serviceCode, int $totalWeight, ?string $originLabel, ?string $destinationLabel): bool
    {
        $serviceCode = strtoupper(trim($serviceCode));
        $config = $this->config ?? [];
        $customRules = $config['custom_rules'] ?? [];

        // Bersihkan label asal (origin) dari format ID jika ada (contoh: 5171::Bojongmanggu, Bandung, Jawa Barat)
        $originProvince = '';
        if ($originLabel) {
            $cleanOrigin = strpos($originLabel, '::') !== false ? explode('::', $originLabel)[1] : $originLabel;
            $originParts = array_map('trim', explode(',', $cleanOrigin));
            if (count($originParts) >= 3) {
                $originProvince = strtoupper(end($originParts));
            }
        }

        // Bersihkan label tujuan (destination) dari format ID jika ada (contoh: 4975::CINGCIN, SOREANG, BANDUNG, JAWA BARAT, 40914)
        $destinationProvince = '';
        if ($destinationLabel) {
            $cleanDest = strpos($destinationLabel, '::') !== false ? explode('::', $destinationLabel)[1] : $destinationLabel;
            $destParts = array_map('trim', explode(',', $cleanDest));
            if (count($destParts) >= 4) {
                // Sesuai urutan: subdistrict, district, city, province, postal_code
                $destinationProvince = strtoupper($destParts[3] ?? '');
            } else {
                $destinationProvince = strtoupper(end($destParts));
            }
        }

        $isLocalProvince = !empty($originProvince) && !empty($destinationProvince) && ($originProvince === $destinationProvince);

        // 1. Evaluasi Aturan Kustom (Custom Rules) dari Admin
        if (!empty($customRules) && is_array($customRules)) {
            foreach ($customRules as $rule) {
                $condition = $rule['condition'] ?? '';
                $action = $rule['action'] ?? 'hide';
                $ruleServices = array_map('strtoupper', array_map('trim', $rule['services'] ?? []));

                // Jika layanan ini tidak ditargetkan oleh aturan saat ini, lewati
                if (!in_array($serviceCode, $ruleServices)) {
                    continue;
                }

                $conditionMet = false;
                switch ($condition) {
                    case 'weight_less_than':
                        $threshold = intval($rule['value'] ?? 0);
                        if ($totalWeight < $threshold) {
                            $conditionMet = true;
                        }
                        break;
                    case 'weight_greater_than':
                        $threshold = intval($rule['value'] ?? 0);
                        if ($totalWeight >= $threshold) {
                            $conditionMet = true;
                        }
                        break;
                    case 'is_local_province':
                        if ($isLocalProvince) {
                            $conditionMet = true;
                        }
                        break;
                    case 'is_outside_province':
                        if (!$isLocalProvince && !empty($originProvince) && !empty($destinationProvince)) {
                            $conditionMet = true;
                        }
                        break;
                }

                if ($conditionMet) {
                    if ($action === 'hide') {
                        return false;
                    } elseif ($action === 'allow_only') {
                        return true;
                    }
                }
            }
        }

        // 2. Evaluasi Aturan Global (Fallback jika tidak ada aturan kustom yang cocok)
        // Aturan Global A: Sembunyikan Kargo jika berat belanjaan di bawah 10 kg (10.000 gram)
        $cargoServices = ['JTR', 'JTR<130', 'JTR>130', 'JTR>200', 'GOKIL', 'CARGO', 'TRC', 'BIGPACK'];
        if ($totalWeight < 10000 && in_array($serviceCode, $cargoServices)) {
            return false;
        }

        // Aturan Global B: Sembunyikan layanan Lokal (seperti JNE CTC, CTCYES) jika tujuan di luar provinsi
        $localServices = ['CTC', 'CTCYES'];
        if (!$isLocalProvince && !empty($originProvince) && !empty($destinationProvince) && in_array($serviceCode, $localServices)) {
            return false;
        }

        return true;
    }
}
