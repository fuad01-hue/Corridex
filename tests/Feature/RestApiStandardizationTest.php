<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RestApiStandardizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customerA;
    protected User $customerB;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;
    protected Shipment $shipmentA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Global Admin',
            'email'    => 'admin@corridex.test',
            'password' => 'password',
            'role'     => 'admin',
        ]);

        $this->operator = User::create([
            'name'     => 'Logistics Operator',
            'email'    => 'operator@corridex.test',
            'password' => 'password',
            'role'     => 'operator',
        ]);

        $this->customerA = User::create([
            'name'     => 'Client Alpha Corp',
            'email'    => 'alpha@client.test',
            'password' => 'password',
            'role'     => 'customer',
        ]);

        $this->customerB = User::create([
            'name'     => 'Client Beta Logistics',
            'email'    => 'beta@client.test',
            'password' => 'password',
            'role'     => 'customer',
        ]);

        $this->carrier = Carrier::create([
            'code'           => 'MSC-SEA',
            'name'           => 'Mediterranean Shipping Company',
            'transport_mode' => 'SEA',
            'is_active'      => true,
        ]);

        $this->origin = Location::create([
            'code'      => 'IDJKT',
            'name'      => 'Jakarta Tanjung Priok Port',
            'city'      => 'Jakarta',
            'country'   => 'Indonesia',
            'region'    => 'Southeast Asia',
            'type'      => 'PORT',
            'latitude'  => -6.1044,
            'longitude' => 106.8827,
        ]);

        $this->destination = Location::create([
            'code'      => 'NLRTM',
            'name'      => 'Rotterdam Port Terminal',
            'city'      => 'Rotterdam',
            'country'   => 'Netherlands',
            'region'    => 'Europe',
            'type'      => 'PORT',
            'latitude'  => 51.9490,
            'longitude' => 4.1450,
        ]);

        $this->shipmentA = Shipment::create([
            'shipment_number'         => 'TRC-2026-SEA-EU-TEST',
            'customer_id'             => $this->customerA->id,
            'carrier_id'              => $this->carrier->id,
            'origin_location_id'      => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'current_location_id'     => $this->origin->id,
            'cargo_type'              => 'Electronics & Precision Components',
            'total_weight_kg'         => 18500.00,
            'total_volume_cbm'        => 64.50,
            'current_status'          => 'IN_TRANSIT',
            'expected_departure'      => Carbon::now()->subDays(2),
            'expected_arrival'        => Carbon::now()->addDays(8),
            'estimated_arrival'       => Carbon::now()->addDays(8),
            'is_delayed'              => false,
            'delay_hours'             => 0.00,
            'is_sla_breached'         => false,
        ]);
    }

    public function test_api_login_returns_standardized_json_structure(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'operator@corridex.test',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Authenticated successfully.')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
                'token',
            ]);
    }

    public function test_api_login_fails_with_standardized_error_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'operator@corridex.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'AUTHENTICATION_FAILED')
            ->assertJsonStructure(['success', 'message', 'errors', 'code']);
    }

    public function test_api_shipments_index_returns_paginated_resource_with_meta(): void
    {
        Sanctum::actingAs($this->operator);

        $response = $this->getJson('/api/shipments');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'shipment_number',
                        'current_status',
                        'geo' => ['origin', 'destination', 'current', 'route_coordinates'],
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_api_shipment_show_includes_formatted_geojson_coordinates_and_iso_timestamps(): void
    {
        Sanctum::actingAs($this->customerA);

        $response = $this->getJson("/api/shipments/{$this->shipmentA->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.shipment_number', $this->shipmentA->shipment_number)
            ->assertJsonPath('data.geo.origin.coordinates.0', (float) $this->origin->latitude)
            ->assertJsonPath('data.geo.origin.coordinates.1', (float) $this->origin->longitude)
            ->assertJsonPath('data.geo.destination.coordinates.0', (float) $this->destination->latitude)
            ->assertJsonPath('data.geo.destination.coordinates.1', (float) $this->destination->longitude);
    }

    public function test_api_shipment_route_endpoint_returns_ordered_waypoints_and_telemetry(): void
    {
        Sanctum::actingAs($this->customerA);

        $response = $this->getJson("/api/shipments/{$this->shipmentA->id}/route");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.shipment_id', $this->shipmentA->id)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'shipment_id',
                    'shipment_number',
                    'current_status',
                    'corridor',
                    'waypoints' => [
                        '*' => ['type', 'label', 'code', 'lat', 'lng', 'coordinates', 'status'],
                    ],
                    'telemetry' => ['hub_name', 'status', 'disclaimer'],
                ],
            ]);
    }

    public function test_customer_cannot_access_foreign_shipment_route(): void
    {
        Sanctum::actingAs($this->customerB);

        $response = $this->getJson("/api/shipments/{$this->shipmentA->id}/route");

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'FORBIDDEN');
    }

    public function test_admin_and_operator_can_access_system_analytics(): void
    {
        // Operator access
        Sanctum::actingAs($this->operator);
        $opResp = $this->getJson('/api/analytics');
        $opResp->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'metrics'   => ['total_shipments', 'in_transit', 'delayed'],
                    'corridors' => ['SEA-EU', 'ASIA-NA', 'SEA-AU'],
                ],
            ]);

        // Customer access forbidden
        Sanctum::actingAs($this->customerA);
        $custResp = $this->getJson('/api/analytics');
        $custResp->assertStatus(403);
    }

    public function test_admin_can_access_audit_logs_via_api_while_others_are_forbidden(): void
    {
        // Admin access
        Sanctum::actingAs($this->admin);
        $adminResp = $this->getJson('/api/audit-logs');
        $adminResp->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);

        // Operator access forbidden
        Sanctum::actingAs($this->operator);
        $opResp = $this->getJson('/api/audit-logs');
        $opResp->assertStatus(403);
    }
}
