<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\TrackingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShipmentLifecycleAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customerA;
    protected User $customerB;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;
    protected TrackingService $trackingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trackingService = app(TrackingService::class);

        $this->admin = User::create([
            'name'     => 'Global Admin',
            'email'    => 'admin@cargotrace.test',
            'password' => 'password',
            'role'     => 'admin',
        ]);

        $this->operator = User::create([
            'name'     => 'Operator User',
            'email'    => 'operator@cargotrace.test',
            'password' => 'password',
            'role'     => 'operator',
        ]);

        $this->customerA = User::create([
            'name'     => 'Customer Alpha',
            'email'    => 'alpha@customer.test',
            'password' => 'password',
            'role'     => 'customer',
        ]);

        $this->customerB = User::create([
            'name'     => 'Customer Beta',
            'email'    => 'beta@customer.test',
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
    }

    private function createShipmentFor(User $customer, string $status = 'CREATED', ?string $number = null): Shipment
    {
        $departure = Carbon::now()->subDays(5);
        $arrival = Carbon::now()->addDays(5);

        return Shipment::create([
            'shipment_number'         => $number ?? ('TRC-TEST-' . uniqid()),
            'customer_id'             => $customer->id,
            'carrier_id'              => $this->carrier->id,
            'origin_location_id'      => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'current_location_id'     => $this->origin->id,
            'cargo_type'              => 'Industrial Machinery',
            'total_weight_kg'         => 12000,
            'total_volume_cbm'        => 45,
            'expected_departure'      => $departure,
            'expected_arrival'        => $arrival,
            'estimated_arrival'       => $arrival,
            'current_status'          => $status,
        ]);
    }

    public function test_valid_status_transitions_through_full_lifecycle(): void
    {
        $shipment = $this->createShipmentFor($this->customerA, 'CREATED');

        // CREATED -> BOOKED
        $this->trackingService->appendEvent($shipment, [
            'event_status' => 'BOOKED',
            'description'  => 'Booking confirmed with carrier.',
            'occurred_at'  => Carbon::now()->subDays(4),
        ], $this->operator);
        $this->assertSame('BOOKED', $shipment->fresh()->current_status);

        // BOOKED -> PICKED_UP
        $this->trackingService->appendEvent($shipment, [
            'event_status' => 'PICKED_UP',
            'description'  => 'Containers loaded at port.',
            'occurred_at'  => Carbon::now()->subDays(3),
        ], $this->operator);
        $this->assertSame('PICKED_UP', $shipment->fresh()->current_status);

        // PICKED_UP -> IN_TRANSIT
        $this->trackingService->appendEvent($shipment, [
            'event_status' => 'IN_TRANSIT',
            'description'  => 'Vessel departed berth.',
            'occurred_at'  => Carbon::now()->subDays(2),
        ], $this->operator);
        $this->assertSame('IN_TRANSIT', $shipment->fresh()->current_status);

        // IN_TRANSIT -> DELIVERED
        $this->trackingService->appendEvent($shipment, [
            'event_status' => 'DELIVERED',
            'description'  => 'Consignment delivered to destination terminal.',
            'occurred_at'  => Carbon::now()->subDay(),
        ], $this->operator);
        $this->assertSame('DELIVERED', $shipment->fresh()->current_status);
    }

    public function test_rejects_invalid_transition_from_delivered(): void
    {
        $shipment = $this->createShipmentFor($this->customerA, 'DELIVERED');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status transition from DELIVERED to IN_TRANSIT.');

        $this->trackingService->appendEvent($shipment, [
            'event_status' => 'IN_TRANSIT',
            'description'  => 'Attempting illegal state rollback.',
        ], $this->operator);
    }

    public function test_rejects_skipping_steps_in_lifecycle(): void
    {
        $shipment = $this->createShipmentFor($this->customerA, 'CREATED');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status transition from CREATED to DELIVERED.');

        $this->trackingService->appendEvent($shipment, [
            'event_status' => 'DELIVERED',
            'description'  => 'Attempting illegal jump to terminal state.',
        ], $this->operator);
    }

    public function test_chronological_integrity_rejects_earlier_timestamp(): void
    {
        $shipment = $this->createShipmentFor($this->customerA, 'CREATED');

        // First event at 2 days ago
        $this->trackingService->appendEvent($shipment, [
            'event_status' => 'BOOKED',
            'description'  => 'Initial booking checkpoint.',
            'occurred_at'  => Carbon::now()->subDays(2),
        ], $this->operator);

        // Second event attempted with timestamp 5 days ago (earlier than latest event)
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Occurred at timestamp cannot be earlier than the shipment's latest tracking event.");

        $this->trackingService->appendEvent($shipment, [
            'event_status' => 'PICKED_UP',
            'description'  => 'Backdated event violating timeline.',
            'occurred_at'  => Carbon::now()->subDays(5),
        ], $this->operator);
    }

    public function test_customer_cannot_view_another_customers_shipment_via_web(): void
    {
        $shipmentB = $this->createShipmentFor($this->customerB);

        $response = $this->actingAs($this->customerA)->get(route('shipments.show', $shipmentB->id));

        $response->assertStatus(403);
    }

    public function test_customer_cannot_view_another_customers_shipment_via_api(): void
    {
        $shipmentB = $this->createShipmentFor($this->customerB);

        Sanctum::actingAs($this->customerA);

        $response = $this->getJson("/api/shipments/{$shipmentB->id}");

        $response->assertStatus(403);
    }

    public function test_customer_can_view_own_shipment_via_web_and_api(): void
    {
        $shipmentA = $this->createShipmentFor($this->customerA);

        // Web View
        $webResponse = $this->actingAs($this->customerA)->get(route('shipments.show', $shipmentA->id));
        $webResponse->assertStatus(200);

        // API Endpoint
        Sanctum::actingAs($this->customerA);
        $apiResponse = $this->getJson("/api/shipments/{$shipmentA->id}");
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonPath('data.shipment_number', $shipmentA->shipment_number);
    }

    public function test_customer_cannot_append_tracking_events(): void
    {
        $shipmentA = $this->createShipmentFor($this->customerA, 'CREATED');

        // Web POST
        $webResponse = $this->actingAs($this->customerA)->post(route('shipments.tracking.store', $shipmentA->id), [
            'event_status' => 'BOOKED',
            'description'  => 'Customer attempt to alter state.',
        ]);
        $webResponse->assertStatus(403);

        // API POST
        Sanctum::actingAs($this->customerA);
        $apiResponse = $this->postJson("/api/shipments/{$shipmentA->id}/tracking", [
            'event_status' => 'BOOKED',
            'description'  => 'Customer API mutation.',
        ]);
        $apiResponse->assertStatus(403);
    }

    public function test_operator_can_append_tracking_events_via_web_and_api(): void
    {
        $shipment = $this->createShipmentFor($this->customerA, 'CREATED');

        // Web Append
        $webResponse = $this->actingAs($this->operator)->post(route('shipments.tracking.store', $shipment->id), [
            'event_status' => 'BOOKED',
            'description'  => 'Operator web tracking event update.',
            'occurred_at'  => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        $webResponse->assertRedirect();
        $this->assertSame('BOOKED', $shipment->fresh()->current_status);

        // API Append
        Sanctum::actingAs($this->operator);
        $apiResponse = $this->postJson("/api/shipments/{$shipment->id}/tracking", [
            'event_status' => 'PICKED_UP',
            'description'  => 'Operator API tracking milestone.',
            'occurred_at'  => Carbon::now()->addHour()->format('Y-m-d H:i:s'),
        ]);
        $apiResponse->assertStatus(201);
        $this->assertSame('PICKED_UP', $shipment->fresh()->current_status);
    }

    public function test_admin_can_access_audit_logs_while_others_are_forbidden(): void
    {
        // Admin can access
        $adminResp = $this->actingAs($this->admin)->get(route('audit-logs.index'));
        $adminResp->assertStatus(200);

        // Operator is forbidden
        $opResp = $this->actingAs($this->operator)->get(route('audit-logs.index'));
        $opResp->assertStatus(403);

        // Customer is forbidden
        $custResp = $this->actingAs($this->customerA)->get(route('audit-logs.index'));
        $custResp->assertStatus(403);
    }

    public function test_dashboard_renders_with_kpi_cards_and_theme_toggle_for_authenticated_users(): void
    {
        $response = $this->actingAs($this->operator)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Operational Visibility Dashboard');
        $response->assertSee('theme-toggle');
        $response->assertSee('lifecycleStatusChart');
        $response->assertSee('carrierVolumeChart');
    }

    public function test_shipments_directory_renders_with_dual_theme_elements_and_filters(): void
    {
        $response = $this->actingAs($this->operator)->get(route('shipments.index'));
        $response->assertStatus(200);
        $response->assertSee('Global Shipment Directory');
        $response->assertSee('Book New Shipment');
        $response->assertSee('Search Identifier');
    }

    public function test_shipment_details_renders_with_leaflet_map_and_print_manifest_controls(): void
    {
        $shipment = $this->createShipmentFor($this->customerA);
        $response = $this->actingAs($this->customerA)->get(route('shipments.show', $shipment->id));
        $response->assertStatus(200);
        $response->assertSee('Print Manifest');
        $response->assertSee('shipment-map');
        $response->assertSee('Route & ETA Telemetry', false);
    }
}
