<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\GeoapifyGeocodingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GeoapifyIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'System Admin',
            'email'    => 'admin@corridex.test',
            'password' => 'password',
            'role'     => 'admin',
        ]);

        $this->operator = User::create([
            'name'     => 'Operations Lead',
            'email'    => 'operator@corridex.test',
            'password' => 'password',
            'role'     => 'operator',
        ]);

        $this->customer = User::create([
            'name'     => 'Consignee User',
            'email'    => 'customer@corridex.test',
            'password' => 'password',
            'role'     => 'customer',
        ]);

        $origin = Location::create([
            'name'         => 'Port of Singapore',
            'code'         => 'SGSIN',
            'city'         => 'Singapore',
            'country'      => 'Singapore',
            'latitude'     => 1.290270,
            'longitude'    => 103.851959,
            'type'         => 'SEAPORT',
        ]);

        $destination = Location::create([
            'name'         => 'Port of Rotterdam',
            'code'         => 'NLRTM',
            'city'         => 'Rotterdam',
            'country'      => 'Netherlands',
            'latitude'     => 51.924420,
            'longitude'    => 4.477733,
            'type'         => 'SEAPORT',
        ]);

        $carrier = Carrier::create([
            'code'           => 'MSC-GLOBAL',
            'name'           => 'Mediterranean Shipping Company',
            'transport_mode' => 'SEA',
            'contact_email'  => 'ops@msc.test',
            'is_active'      => true,
        ]);

        $departure = Carbon::now()->subDays(5);
        $arrival = Carbon::now()->addDays(5);

        $this->shipment = Shipment::create([
            'shipment_number'         => 'TRC-GEO-TEST-001',
            'customer_id'             => $this->customer->id,
            'carrier_id'              => $carrier->id,
            'origin_location_id'      => $origin->id,
            'destination_location_id' => $destination->id,
            'current_location_id'     => $origin->id,
            'cargo_type'              => 'Consumer Electronics',
            'total_weight_kg'         => 18500,
            'total_volume_cbm'        => 65,
            'expected_departure'      => $departure,
            'expected_arrival'        => $arrival,
            'estimated_arrival'       => $arrival,
            'current_status'          => 'IN_TRANSIT',
        ]);
    }

    /**
     * Test 1: Service successfully parses and normalizes Geoapify response.
     */
    public function test_service_successfully_geocodes_and_normalizes_results(): void
    {
        Config::set('services.geoapify.key', 'fake-geoapify-key');

        Http::fake([
            'https://api.geoapify.com/v1/geocode/search*' => Http::response([
                'results' => [
                    [
                        'name'         => 'Port of Rotterdam',
                        'city'         => 'Rotterdam',
                        'country'      => 'Netherlands',
                        'country_code' => 'nl',
                        'lat'          => 51.92442,
                        'lon'          => 4.47773,
                        'formatted'    => 'Port of Rotterdam, Rotterdam, Netherlands',
                    ],
                    [
                        'name'         => 'Singapore Container Terminal',
                        'city'         => 'Singapore',
                        'country'      => 'Singapore',
                        'country_code' => 'sg',
                        'lat'          => 1.28000,
                        'lon'          => 103.85000,
                        'formatted'    => 'Singapore Container Terminal, Singapore',
                    ],
                ],
            ], 200),
        ]);

        $service = new GeoapifyGeocodingService();
        $results = $service->geocode('Rotterdam Port', 2);

        $this->assertCount(2, $results);

        $first = $results[0];
        $this->assertSame('Port of Rotterdam', $first['name']);
        $this->assertSame('Rotterdam', $first['city']);
        $this->assertSame('Netherlands', $first['country']);
        $this->assertSame('NL', $first['country_code']);
        $this->assertEquals(51.92442, $first['latitude']);
        $this->assertEquals(4.47773, $first['longitude']);
        $this->assertSame('Port of Rotterdam, Rotterdam, Netherlands', $first['formatted']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.geoapify.com/v1/geocode/search')
                && $request['text'] === 'Rotterdam Port'
                && $request['apiKey'] === 'fake-geoapify-key'
                && $request['format'] === 'json';
        });
    }

    /**
     * Test 2: Service gracefully handles HTTP 500 error without crashing.
     */
    public function test_service_gracefully_handles_upstream_500_error(): void
    {
        Config::set('services.geoapify.key', 'fake-geoapify-key');

        Http::fake([
            'https://api.geoapify.com/v1/geocode/search*' => Http::response(['message' => 'Internal server error'], 500),
        ]);

        $service = new GeoapifyGeocodingService();
        $results = $service->geocode('Singapore');

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /**
     * Test 3: Service gracefully handles network timeout without throwing.
     */
    public function test_service_gracefully_handles_network_timeout(): void
    {
        Config::set('services.geoapify.key', 'fake-geoapify-key');

        Http::fake([
            'https://api.geoapify.com/v1/geocode/search*' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out');
            },
        ]);

        $service = new GeoapifyGeocodingService();
        $results = $service->geocode('Hamburg Port');

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /**
     * Test 4: Service skips HTTP call when GEOAPIFY_API_KEY is empty.
     */
    public function test_service_skips_call_when_api_key_is_empty(): void
    {
        Config::set('services.geoapify.key', '');

        Http::fake();

        $service = new GeoapifyGeocodingService();
        $results = $service->geocode('Rotterdam');

        $this->assertIsArray($results);
        $this->assertEmpty($results);
        Http::assertNothingSent();
    }

    /**
     * Test 5: API endpoint /api/locations/geocode requires authentication.
     */
    public function test_geocode_endpoint_rejects_unauthenticated_requests(): void
    {
        $response = $this->getJson('/api/locations/geocode?text=Singapore');
        $response->assertStatus(401);
    }

    /**
     * Test 6: API endpoint /api/locations/geocode rejects customer role (403 Forbidden).
     */
    public function test_geocode_endpoint_forbids_customer_role(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/locations/geocode?text=Singapore');
        $response->assertStatus(403);
    }

    /**
     * Test 7: API endpoint /api/locations/geocode allows operator role.
     */
    public function test_geocode_endpoint_allows_operator_role_with_mocked_results(): void
    {
        Config::set('services.geoapify.key', 'test-operator-key');

        Http::fake([
            'https://api.geoapify.com/v1/geocode/search*' => Http::response([
                'results' => [
                    [
                        'name'         => 'Port of Busan',
                        'city'         => 'Busan',
                        'country'      => 'South Korea',
                        'country_code' => 'kr',
                        'lat'          => 35.1028,
                        'lon'          => 129.0403,
                        'formatted'    => 'Port of Busan, South Korea',
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($this->operator);

        $response = $this->getJson('/api/locations/geocode?text=Busan&limit=1');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'Port of Busan')
            ->assertJsonPath('data.0.city', 'Busan')
            ->assertJsonPath('data.0.country_code', 'KR')
            ->assertJsonPath('meta.count', 1);
    }

    /**
     * Test 8: API endpoint /api/locations/geocode allows admin role.
     */
    public function test_geocode_endpoint_allows_admin_role(): void
    {
        Config::set('services.geoapify.key', 'test-admin-key');

        Http::fake([
            'https://api.geoapify.com/v1/geocode/search*' => Http::response([
                'results' => [],
            ], 200),
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/locations/geocode?query=Tokyo');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', []);
    }

    /**
     * Test 9: API endpoint validates missing query parameter.
     */
    public function test_geocode_endpoint_validates_empty_query(): void
    {
        Sanctum::actingAs($this->operator);

        $response = $this->getJson('/api/locations/geocode');
        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'VALIDATION_ERROR');
    }

    /**
     * Test 10: Blade shipment view displays Geoapify badge when key is configured.
     */
    public function test_shipment_view_renders_geoapify_badge_when_key_set(): void
    {
        Config::set('services.geoapify.key', 'demo-geoapify-key-xyz');

        $response = $this->actingAs($this->admin)->get(route('shipments.show', $this->shipment->id));

        $response->assertStatus(200);
        $response->assertSee('Geoapify Maritime Vector');
        $response->assertSee('maps.geoapify.com/v1/tile/dark-matter');
        $response->assertSee('maps.geoapify.com/v1/tile/positron');
    }

    /**
     * Test 11: Blade shipment view falls back to Esri Maritime Canvas when key is missing.
     */
    public function test_shipment_view_falls_back_to_esri_when_key_is_empty(): void
    {
        Config::set('services.geoapify.key', '');

        $response = $this->actingAs($this->admin)->get(route('shipments.show', $this->shipment->id));

        $response->assertStatus(200);
        $response->assertSee('Esri Maritime Canvas');
        $response->assertSee('server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile');
        $response->assertSee('server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile');
    }

    /**
     * Test 12: Blade dashboard view renders dynamic map badge and Geoapify tile script.
     */
    public function test_dashboard_view_renders_dual_theme_map_tiles(): void
    {
        Config::set('services.geoapify.key', 'dashboard-geoapify-key');

        $response = $this->actingAs($this->operator)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Geoapify Maritime Vector');
        $response->assertSee('maps.geoapify.com/v1/tile/dark-matter');
        $response->assertSee('maps.geoapify.com/v1/tile/positron');
    }
}
