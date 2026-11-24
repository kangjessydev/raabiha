<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Services\BinderByteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BinderByteShippingResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        SiteSetting::updateOrCreate(['key' => 'binderbyte_api_key'], ['value' => 'test-bb-key']);
    }

    public function test_resolves_kemendagri_district_codes_to_matching_binderbyte_village_ids_and_calculates_cost(): void
    {
        $capturedCostPayload = null;

        Http::fake([
            'https://api.binderbyte.com/v1/locations*search=pameungpeuk*' => Http::response([
                'code' => '200',
                'data' => [
                    ['id' => 'village_5822', 'type' => 'village', 'label' => 'Pameungpeuk, Pameungpeuk, Garut, Jawa Barat, 44175'],
                    ['id' => 'village_5170', 'type' => 'village', 'label' => 'Bojongkunci, Pameungpeuk, Bandung, Jawa Barat, 40376'],
                ],
            ], 200),
            'https://api.binderbyte.com/v1/locations*search=soreang*' => Http::response([
                'code' => '200',
                'data' => [
                    ['id' => 'village_82025', 'type' => 'village', 'label' => 'Wattang Soreang, Soreang, Parepare, Sulawesi Selatan, 91132'],
                    ['id' => 'village_4975', 'type' => 'village', 'label' => 'Soreang, Soreang, Bandung, Jawa Barat, 40911'],
                ],
            ], 200),
            'https://api.binderbyte.com/v1/cost*' => function ($request) use (&$capturedCostPayload) {
                $capturedCostPayload = $request->data();

                return Http::response([
                    'code' => '200',
                    'data' => [
                        'results' => [
                            [
                                'code' => 'jnt',
                                'name' => 'J&T Express',
                                'costs' => [
                                    ['code' => 'jnt', 'service' => 'EZ', 'type' => 'Reguler', 'price' => '7000', 'estimated' => '-'],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $result = BinderByteService::getShippingCost(
            origin: '32.04.14',
            destination: '32.04.37',
            weight: 1000,
            couriers: 'jnt',
            originLabel: 'Pameungpeuk, KAB. BANDUNG, JAWA BARAT',
            destinationLabel: 'Soreang, KAB. BANDUNG, JAWA BARAT'
        );

        $this->assertNotNull($capturedCostPayload);
        $this->assertSame('village_5170', $capturedCostPayload['origin']);
        $this->assertSame('village_4975', $capturedCostPayload['destination']);
        $this->assertSame('7000', $result['results'][0]['costs'][0]['price'] ?? null);
    }

    public function test_failed_cost_api_response_is_not_cached_for_six_hours(): void
    {
        $attempt = 0;

        Http::fake([
            'https://api.binderbyte.com/v1/cost*' => function () use (&$attempt) {
                $attempt++;
                if ($attempt === 1) {
                    return Http::response(['code' => '500', 'message' => 'Temporary Error'], 500);
                }

                return Http::response([
                    'code' => '200',
                    'data' => [
                        'results' => [
                            [
                                'code' => 'jnt',
                                'name' => 'J&T Express',
                                'costs' => [
                                    ['code' => 'jnt', 'service' => 'EZ', 'price' => '7000', 'estimated' => '-'],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $first = BinderByteService::getShippingCost('village_5171', 'village_4975', 1000, 'jnt');
        $this->assertSame([], $first);

        $second = BinderByteService::getShippingCost('village_5171', 'village_4975', 1000, 'jnt');
        $this->assertSame('7000', $second['results'][0]['costs'][0]['price'] ?? null);
        $this->assertSame(2, $attempt);
    }
}
