<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocationSearchAndSeederTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@corridex.test',
            'password' => 'password',
            'role'     => 'admin',
        ]);

        $this->operator = User::create([
            'name'     => 'Operator User',
            'email'    => 'operator@corridex.test',
            'password' => 'password',
            'role'     => 'operator',
        ]);

        $this->customer = User::create([
            'name'     => 'Customer User',
            'email'    => 'customer@corridex.test',
            'password' => 'password',
            'role'     => 'customer',
        ]);
    }

    public function test_location_seeder_populates_aceh_indonesian_and_global_ports_idempotently(): void
    {
        $this->seed(LocationSeeder::class);
        $firstCount = Location::count();

        // Re-running must not duplicate records
        $this->seed(LocationSeeder::class);
        $this->assertSame($firstCount, Location::count());
        $this->assertGreaterThanOrEqual(100, $firstCount);

        $lsw = Location::where('code', 'IDLSW')->first();
        $this->assertNotNull($lsw);
        $this->assertEqualsWithDelta(5.2465, $lsw->latitude, 0.0001);
        $this->assertEqualsWithDelta(97.0398, $lsw->longitude, 0.0001);

        $bna = Location::where('code', 'IDBNA')->first();
        $this->assertNotNull($bna);
        $this->assertEqualsWithDelta(5.5975, $bna->latitude, 0.0001);
        $this->assertEqualsWithDelta(95.5125, $bna->longitude, 0.0001);

        $sbg = Location::where('code', 'IDSBG')->first();
        $this->assertNotNull($sbg);
        $this->assertEqualsWithDelta(5.8835, $sbg->latitude, 0.0001);
        $this->assertEqualsWithDelta(95.3210, $sbg->longitude, 0.0001);

        foreach (['IDBLW', 'IDDUM', 'IDJKT', 'IDSUB', 'IDBIT', 'IDSOQ', 'IDMAK'] as $code) {
            $this->assertDatabaseHas('locations', ['code' => $code]);
        }
    }

    public function test_locations_search_requires_authentication_and_returns_matching_ports(): void
    {
        $this->getJson('/locations/search?q=IDLSW')->assertStatus(401);

        $this->seed(LocationSeeder::class);

        $response = $this->actingAs($this->operator)->getJson('/locations/search?q=IDLSW');
        $response->assertStatus(200)
            ->assertJsonPath('data.0.code', 'IDLSW')
            ->assertJsonPath('data.0.source', 'local');

        $acehResp = $this->actingAs($this->operator)->getJson('/locations/search?q=Banda+Aceh');
        $acehResp->assertStatus(200)
            ->assertJsonFragment(['code' => 'IDBNA']);

        $limitResp = $this->actingAs($this->operator)->getJson('/locations/search?q=');
        $limitResp->assertStatus(200);
        $this->assertLessThanOrEqual(25, count($limitResp->json('data')));
    }

    public function test_locations_search_falls_back_to_geoapify_autocomplete_when_local_matches_under_five(): void
    {
        Config::set('services.geoapify.key', 'test-geoapify-key');

        Http::fake([
            'https://api.geoapify.com/v1/geocode/autocomplete*' => Http::response([
                'results' => [
                    [
                        'name'         => 'Port of Ulsan',
                        'city'         => 'Ulsan',
                        'country'      => 'South Korea',
                        'country_code' => 'kr',
                        'lat'          => 35.5015,
                        'lon'          => 129.3833,
                        'formatted'    => 'Port of Ulsan, South Korea',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->operator)->getJson('/locations/search?q=Ulsan');
        $response->assertStatus(200)
            ->assertJsonPath('meta.local_count', 0)
            ->assertJsonPath('meta.geoapify_count', 1)
            ->assertJsonPath('data.0.name', 'Port of Ulsan')
            ->assertJsonPath('data.0.source', 'geoapify');
    }

    public function test_locations_resolve_persists_or_deduplicates_port_for_operators(): void
    {
        $this->actingAs($this->customer)->postJson('/locations/resolve', [
            'name'      => 'Port of Ulsan',
            'latitude'  => 35.5015,
            'longitude' => 129.3833,
        ])->assertStatus(403);

        $first = $this->actingAs($this->operator)->postJson('/locations/resolve', [
            'name'      => 'Port of Ulsan',
            'city'      => 'Ulsan',
            'country'   => 'South Korea',
            'code'      => 'KRUSN',
            'latitude'  => 35.5015,
            'longitude' => 129.3833,
        ]);
        $first->assertStatus(201)->assertJsonPath('data.code', 'KRUSN');
        $createdId = $first->json('data.id');

        $second = $this->actingAs($this->operator)->postJson('/locations/resolve', [
            'name'      => 'Port of Ulsan',
            'city'      => 'Ulsan',
            'country'   => 'South Korea',
            'latitude'  => 35.5015,
            'longitude' => 129.3833,
        ]);
        $second->assertStatus(200)->assertJsonPath('data.id', $createdId);
        $this->assertSame(1, Location::where('name', 'Port of Ulsan')->count());
    }

    public function test_shipments_views_render_searchable_port_comboboxes(): void
    {
        $this->seed(LocationSeeder::class);

        $indexResp = $this->actingAs($this->operator)->get(route('shipments.index'));
        $indexResp->assertStatus(200)
            ->assertSee('portCombobox(')
            ->assertSee('booking-origin-location')
            ->assertSee('booking-destination-location');

        $origin = Location::where('code', 'IDLSW')->firstOrFail();
        $dest = Location::where('code', 'NLRTM')->firstOrFail();
        $carrier = Carrier::create([
            'code'           => 'MSC-TEST',
            'name'           => 'MSC Test',
            'transport_mode' => 'SEA',
            'is_active'      => true,
        ]);

        $shipment = Shipment::create([
            'shipment_number'         => 'TRC-ACEH-001',
            'customer_id'             => $this->customer->id,
            'carrier_id'              => $carrier->id,
            'origin_location_id'      => $origin->id,
            'destination_location_id' => $dest->id,
            'current_location_id'     => $origin->id,
            'cargo_type'              => 'LNG Equipment',
            'total_weight_kg'         => 5000,
            'total_volume_cbm'        => 20,
            'expected_departure'      => Carbon::now()->subDay(),
            'expected_arrival'        => Carbon::now()->addDays(10),
            'estimated_arrival'       => Carbon::now()->addDays(10),
            'current_status'          => 'IN_TRANSIT',
        ]);

        $showResp = $this->actingAs($this->operator)->get(route('shipments.show', $shipment->id));
        $showResp->assertStatus(200)
            ->assertSee('portCombobox(')
            ->assertSee('tracking-location-select');
    }
}
