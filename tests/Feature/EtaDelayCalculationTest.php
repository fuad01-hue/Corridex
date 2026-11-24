<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\EtaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtaDelayCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected EtaService $etaService;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->etaService = new EtaService();

        $this->customer = User::create([
            'name'     => 'Consignee Test',
            'email'    => 'consignee@example.com',
            'password' => 'password',
            'role'     => 'customer',
        ]);

        $this->carrier = Carrier::create([
            'code'           => 'MSC-TEST',
            'name'           => 'Mediterranean Shipping Co',
            'transport_mode' => 'SEA',
            'is_active'      => true,
        ]);

        $this->origin = Location::create([
            'code'      => 'IDJKT',
            'name'      => 'Jakarta Port',
            'city'      => 'Jakarta',
            'country'   => 'Indonesia',
            'region'    => 'Southeast Asia',
            'type'      => 'PORT',
            'latitude'  => -6.1044,
            'longitude' => 106.8827,
        ]);

        $this->destination = Location::create([
            'code'      => 'NLRTM',
            'name'      => 'Rotterdam Port',
            'city'      => 'Rotterdam',
            'country'   => 'Netherlands',
            'region'    => 'Europe',
            'type'      => 'PORT',
            'latitude'  => 51.9490,
            'longitude' => 4.1450,
        ]);
    }

    private function createSampleShipment(string $shipmentNumber, Carbon $expectedArrival): Shipment
    {
        return Shipment::create([
            'shipment_number'         => $shipmentNumber,
            'customer_id'             => $this->customer->id,
            'carrier_id'              => $this->carrier->id,
            'origin_location_id'      => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'current_location_id'     => $this->origin->id,
            'cargo_type'              => 'Electronics',
            'total_weight_kg'         => 1000,
            'total_volume_cbm'        => 10,
            'expected_departure'      => $expectedArrival->copy()->subDays(5),
            'expected_arrival'        => $expectedArrival,
            'estimated_arrival'       => $expectedArrival,
            'current_status'          => 'IN_TRANSIT',
        ]);
    }

    public function test_on_time_shipment_has_no_delay_and_none_severity(): void
    {
        $expected = Carbon::parse('2026-11-20 12:00:00');
        $shipment = $this->createSampleShipment('TRC-2026-ONTIME-001', $expected);

        // Same time
        $result = $this->etaService->evaluateDelay($shipment, $expected);
        $this->assertFalse($result['is_delayed']);
        $this->assertSame(0, $result['delay_hours']);
        $this->assertSame('NONE', $result['delay_severity']);
        $this->assertFalse($result['is_sla_breached']);

        // Early arrival
        $early = $expected->copy()->subHours(5);
        $resultEarly = $this->etaService->evaluateDelay($shipment, $early);
        $this->assertFalse($resultEarly['is_delayed']);
        $this->assertSame(0, $resultEarly['delay_hours']);
        $this->assertSame('NONE', $resultEarly['delay_severity']);
        $this->assertFalse($resultEarly['is_sla_breached']);
    }

    public function test_low_delay_between_1_and_6_hours(): void
    {
        $expected = Carbon::parse('2026-11-20 12:00:00');
        $shipment = $this->createSampleShipment('TRC-2026-LOW-002', $expected);

        // 4 hours delay
        $eta = $expected->copy()->addHours(4);
        $result = $this->etaService->evaluateDelay($shipment, $eta);

        $this->assertTrue($result['is_delayed']);
        $this->assertSame(4, $result['delay_hours']);
        $this->assertSame('LOW', $result['delay_severity']);
        $this->assertFalse($result['is_sla_breached']);
    }

    public function test_medium_delay_between_7_and_24_hours(): void
    {
        $expected = Carbon::parse('2026-11-20 12:00:00');
        $shipment = $this->createSampleShipment('TRC-2026-MED-003', $expected);

        // 16 hours delay
        $eta = $expected->copy()->addHours(16);
        $result = $this->etaService->evaluateDelay($shipment, $eta);

        $this->assertTrue($result['is_delayed']);
        $this->assertSame(16, $result['delay_hours']);
        $this->assertSame('MEDIUM', $result['delay_severity']);
    }

    public function test_high_delay_exceeding_24_hours(): void
    {
        $expected = Carbon::parse('2026-11-20 12:00:00');
        $shipment = $this->createSampleShipment('TRC-2026-HIGH-004', $expected);

        // 48 hours delay
        $eta = $expected->copy()->addHours(48);
        $result = $this->etaService->evaluateDelay($shipment, $eta);

        $this->assertTrue($result['is_delayed']);
        $this->assertSame(48, $result['delay_hours']);
        $this->assertSame('HIGH', $result['delay_severity']);
        $this->assertTrue($result['is_sla_breached']);
    }

    public function test_custom_sla_threshold_evaluation(): void
    {
        $expected = Carbon::parse('2026-11-20 12:00:00');
        $shipment = $this->createSampleShipment('TRC-2026-CUSTOM-005', $expected);

        // 14 hours delay
        $eta = $expected->copy()->addHours(14);

        // With 12h threshold -> breaches SLA
        $result12 = $this->etaService->evaluateDelay($shipment, $eta, 12);
        $this->assertTrue($result12['is_sla_breached']);

        // With 18h threshold -> does not breach SLA
        $result18 = $this->etaService->evaluateDelay($shipment, $eta, 18);
        $this->assertFalse($result18['is_sla_breached']);
    }

    public function test_corridor_threshold_detection_from_shipment_code(): void
    {
        $expected = Carbon::parse('2026-11-20 12:00:00');

        // Corridor SEA-EU (12h threshold)
        $shpSeaEu = $this->createSampleShipment('TRC-2026-SEA-EU-001', $expected);
        $resultSeaEu = $this->etaService->evaluateDelay($shpSeaEu, $expected->copy()->addHours(13));
        $this->assertTrue($resultSeaEu['is_sla_breached'], '13h delay should breach 12h SEA-EU SLA');

        // Corridor ASIA-NA (18h threshold)
        $shpAsiaNa = $this->createSampleShipment('TRC-2026-ASIA-NA-002', $expected);
        $resultAsiaNaUnder = $this->etaService->evaluateDelay($shpAsiaNa, $expected->copy()->addHours(16));
        $this->assertFalse($resultAsiaNaUnder['is_sla_breached'], '16h delay should not breach 18h ASIA-NA SLA');

        $resultAsiaNaOver = $this->etaService->evaluateDelay($shpAsiaNa, $expected->copy()->addHours(20));
        $this->assertTrue($resultAsiaNaOver['is_sla_breached'], '20h delay should breach 18h ASIA-NA SLA');

        // Corridor SEA-AU (24h threshold)
        $shpSeaAu = $this->createSampleShipment('TRC-2026-SEA-AU-003', $expected);
        $resultSeaAu = $this->etaService->evaluateDelay($shpSeaAu, $expected->copy()->addHours(22));
        $this->assertFalse($resultSeaAu['is_sla_breached'], '22h delay should not breach 24h SEA-AU SLA');
    }
}
