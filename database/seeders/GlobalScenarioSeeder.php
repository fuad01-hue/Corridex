<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GlobalScenarioSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------------------
        // 1. Core Role-Based Accounts
        // ---------------------------------------------------------------------
        $admin = User::updateOrCreate(['email' => 'admin@cargotrace.test'], [
            'name'     => 'Global Administrator',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        $operator = User::updateOrCreate(['email' => 'operator@cargotrace.test'], [
            'name'     => 'Global Logistics Operator',
            'password' => Hash::make('password'),
            'role'     => 'operator',
        ]);

        $customer1 = User::updateOrCreate(['email' => 'customer@cargotrace.test'], [
            'name'     => 'Nusantara Global Exports Ltd',
            'password' => Hash::make('password'),
            'role'     => 'customer',
        ]);

        $customer2 = User::updateOrCreate(['email' => 'pacific@cargotrace.test'], [
            'name'     => 'Pacific Tech Manufacturing Inc',
            'password' => Hash::make('password'),
            'role'     => 'customer',
        ]);

        // ---------------------------------------------------------------------
        // 2. Multimodal Carriers
        // ---------------------------------------------------------------------
        $msc = Carrier::updateOrCreate(['code' => 'MSC-SEA'], [
            'name'           => 'Mediterranean Shipping Company',
            'transport_mode' => 'SEA',
            'contact_email'  => 'ops@msc-global.test',
            'is_active'      => true,
        ]);

        $maersk = Carrier::updateOrCreate(['code' => 'MAERSK-SEA'], [
            'name'           => 'Maersk Line Ocean Freight',
            'transport_mode' => 'SEA',
            'contact_email'  => 'control@maersk-global.test',
            'is_active'      => true,
        ]);

        $dhlAir = Carrier::updateOrCreate(['code' => 'DHL-AIR'], [
            'name'           => 'DHL Global Air Cargo',
            'transport_mode' => 'AIR',
            'contact_email'  => 'airfreight@dhl-global.test',
            'is_active'      => true,
        ]);

        // ---------------------------------------------------------------------
        // 3. Global Maritime Ports & Transit Hubs
        // ---------------------------------------------------------------------
        $jkt = Location::updateOrCreate(['code' => 'IDJKT'], [
            'name'      => 'Jakarta Tanjung Priok Port',
            'city'      => 'Jakarta',
            'country'   => 'Indonesia',
            'region'    => 'Southeast Asia',
            'type'      => 'PORT',
            'latitude'  => -6.0938000,
            'longitude' => 106.8878000,
        ]);

        $sin = Location::updateOrCreate(['code' => 'SGSIN'], [
            'name'      => 'Singapore Tuas Mega Port',
            'city'      => 'Singapore',
            'country'   => 'Singapore',
            'region'    => 'Southeast Asia',
            'type'      => 'PORT',
            'latitude'  => 1.2550000,
            'longitude' => 103.6210000,
        ]);

        $suez = Location::updateOrCreate(['code' => 'EGSUZ'], [
            'name'      => 'Suez Canal Transit Hub',
            'city'      => 'Suez',
            'country'   => 'Egypt',
            'region'    => 'Middle East / Africa',
            'type'      => 'HUB',
            'latitude'  => 29.9355000,
            'longitude' => 32.5618000,
        ]);

        $rtm = Location::updateOrCreate(['code' => 'NLRTM'], [
            'name'      => 'Rotterdam Port Terminal',
            'city'      => 'Rotterdam',
            'country'   => 'Netherlands',
            'region'    => 'Europe',
            'type'      => 'PORT',
            'latitude'  => 51.9490000,
            'longitude' => 4.1450000,
        ]);

        $lax = Location::updateOrCreate(['code' => 'USLAX'], [
            'name'      => 'Port of Los Angeles',
            'city'      => 'Los Angeles',
            'country'   => 'United States',
            'region'    => 'North America',
            'type'      => 'PORT',
            'latitude'  => 33.7292000,
            'longitude' => -118.2620000,
        ]);

        $syd = Location::updateOrCreate(['code' => 'AUSYD'], [
            'name'      => 'Port Botany Sydney',
            'city'      => 'Sydney',
            'country'   => 'Australia',
            'region'    => 'Oceania',
            'type'      => 'PORT',
            'latitude'  => -33.9715000,
            'longitude' => 151.2165000,
        ]);

        // ---------------------------------------------------------------------
        // 4. Scenario 1 (Corridor A): SEA -> EU (Jakarta -> Rotterdam)
        // 48h delay, breaches 12h threshold, HIGH severity, active CUSTOMS_HOLD
        // ---------------------------------------------------------------------
        $shp1 = Shipment::updateOrCreate(['shipment_number' => 'TRC-2026-SEA-EU-001'], [
            'customer_id'             => $customer1->id,
            'carrier_id'              => $msc->id,
            'origin_location_id'      => $jkt->id,
            'destination_location_id' => $rtm->id,
            'current_location_id'     => $suez->id,
            'cargo_type'              => 'Industrial Electronics & High-Voltage Inverters',
            'total_weight_kg'         => 18500.00,
            'total_volume_cbm'        => 68.50,
            'current_status'          => 'IN_TRANSIT',
            'expected_departure'      => Carbon::now()->subDays(10),
            'expected_arrival'        => Carbon::now()->addDays(4),
            'estimated_arrival'       => Carbon::now()->addDays(6), // 48 hours delay
            'is_delayed'              => true,
            'delay_hours'             => 48,
            'delay_severity'          => 'HIGH',
            'is_sla_breached'         => true,
            'delay_reason'            => 'Supplementary customs manifest audit and congestion backlog at Suez Canal Transit Hub (Corridor SEA-EU SLA threshold: 12h).',
        ]);

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shp1->id, 'sku' => 'SKU-ELC-01'],
            [
                'item_name'  => 'Smart Inverter Power Modules',
                'quantity'   => 150,
                'weight_kg'  => 10500.00,
                'volume_cbm' => 40.00,
            ]
        );

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shp1->id, 'sku' => 'SKU-ELC-02'],
            [
                'item_name'  => 'Microprocessor Sub-assemblies',
                'quantity'   => 300,
                'weight_kg'  => 8000.00,
                'volume_cbm' => 28.50,
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shp1->id, 'event_status' => 'PICKED_UP', 'location_id' => $jkt->id],
            [
                'recorded_by'  => $operator->id,
                'latitude'     => $jkt->latitude,
                'longitude'    => $jkt->longitude,
                'description'  => 'Cargo inspected, containerized, and loaded onto MSC vessel at Tanjung Priok Port.',
                'is_exception' => false,
                'occurred_at'  => Carbon::now()->subDays(10),
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shp1->id, 'event_status' => 'IN_TRANSIT', 'location_id' => $sin->id],
            [
                'recorded_by'  => $operator->id,
                'latitude'     => $sin->latitude,
                'longitude'    => $sin->longitude,
                'description'  => 'Scheduled transshipment and bunkering operations completed at Singapore Tuas Mega Port.',
                'is_exception' => false,
                'occurred_at'  => Carbon::now()->subDays(7),
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shp1->id, 'event_status' => 'IN_TRANSIT', 'location_id' => $suez->id],
            [
                'recorded_by'  => $operator->id,
                'latitude'     => $suez->latitude,
                'longitude'    => $suez->longitude,
                'description'  => 'Transit clearance hold initiated at Suez Canal Transit Hub pending regulatory manifest audit.',
                'is_exception' => true,
                'occurred_at'  => Carbon::now()->subDays(1),
            ]
        );

        ShipmentException::updateOrCreate(
            ['shipment_id' => $shp1->id, 'exception_type' => 'CUSTOMS_HOLD'],
            [
                'reported_by'      => $operator->id,
                'severity'         => 'HIGH',
                'status'           => 'OPEN',
                'description'      => 'Cross-regional technical documentation verification initiated by port customs authorities.',
                'resolution_notes' => 'Awaiting consular verification certificate from exporting jurisdiction.',
                'detected_at'      => Carbon::now()->subHours(20),
                'resolved_at'      => null,
            ]
        );

        Notification::updateOrCreate(
            ['user_id' => $customer1->id, 'shipment_id' => $shp1->id, 'type' => 'DELAY_ALERT'],
            [
                'title'   => 'SLA Breach Alert: TRC-2026-SEA-EU-001',
                'message' => 'Shipment TRC-2026-SEA-EU-001 is delayed by 48 hours at Suez Canal Transit Hub, exceeding the 12-hour corridor SLA.',
                'is_read' => false,
            ]
        );

        AuditLog::create([
            'user_id'     => $operator->id,
            'action'      => 'SEED_SHIPMENT_SCENARIO_A',
            'entity_type' => 'Shipment',
            'entity_id'   => $shp1->id,
            'old_values'  => null,
            'new_values'  => ['status' => 'IN_TRANSIT', 'delay_hours' => 48, 'sla_breach' => true],
            'ip_address'  => '127.0.0.1',
            'created_at'  => Carbon::now()->subDays(1),
        ]);

        // ---------------------------------------------------------------------
        // 5. Scenario 2 (Corridor B): ASIA -> NA (Singapore -> Los Angeles)
        // 20h delay, breaches 18h threshold, MEDIUM severity, WEATHER_DELAY
        // ---------------------------------------------------------------------
        $shp2 = Shipment::updateOrCreate(['shipment_number' => 'TRC-2026-ASIA-NA-002'], [
            'customer_id'             => $customer2->id,
            'carrier_id'              => $maersk->id,
            'origin_location_id'      => $sin->id,
            'destination_location_id' => $lax->id,
            'current_location_id'     => $sin->id,
            'cargo_type'              => 'Semiconductor Wafer Trays & Server Racks',
            'total_weight_kg'         => 9400.00,
            'total_volume_cbm'        => 42.00,
            'current_status'          => 'IN_TRANSIT',
            'expected_departure'      => Carbon::now()->subDays(8),
            'expected_arrival'        => Carbon::now()->addDays(5),
            'estimated_arrival'       => Carbon::now()->addDays(5)->addHours(20), // 20 hours delay
            'is_delayed'              => true,
            'delay_hours'             => 20,
            'delay_severity'          => 'MEDIUM',
            'is_sla_breached'         => true,
            'delay_reason'            => 'Vessel cruising speed reduction due to adverse weather system in the North Pacific Ocean (Corridor ASIA-NA SLA threshold: 18h).',
        ]);

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shp2->id, 'sku' => 'SKU-SMC-101'],
            [
                'item_name'  => '300mm Silicon Wafer Carriers',
                'quantity'   => 80,
                'weight_kg'  => 4200.00,
                'volume_cbm' => 18.00,
            ]
        );

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shp2->id, 'sku' => 'SKU-SRV-202'],
            [
                'item_name'  => 'High-Density Edge Server Enclosures',
                'quantity'   => 50,
                'weight_kg'  => 5200.00,
                'volume_cbm' => 24.00,
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shp2->id, 'event_status' => 'PICKED_UP', 'location_id' => $sin->id],
            [
                'recorded_by'  => $operator->id,
                'latitude'     => $sin->latitude,
                'longitude'    => $sin->longitude,
                'description'  => 'Departure clearance completed; container sealed and loaded for transpacific voyage.',
                'is_exception' => false,
                'occurred_at'  => Carbon::now()->subDays(8),
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shp2->id, 'event_status' => 'IN_TRANSIT', 'location_id' => null],
            [
                'recorded_by'  => $operator->id,
                'latitude'     => 21.3069000,
                'longitude'    => -157.8583000,
                'description'  => 'Vessel cruising speed reduced to 12 knots near Hawaiian waypoint due to storm swell.',
                'is_exception' => true,
                'occurred_at'  => Carbon::now()->subDays(2),
            ]
        );

        ShipmentException::updateOrCreate(
            ['shipment_id' => $shp2->id, 'exception_type' => 'WEATHER_DELAY'],
            [
                'reported_by'      => $operator->id,
                'severity'         => 'MEDIUM',
                'status'           => 'INVESTIGATING',
                'description'      => 'Severe gale and 6-meter swell advisory along the great circle shipping lane.',
                'resolution_notes' => 'Route optimized 120 nautical miles south to avoid the low-pressure system core.',
                'detected_at'      => Carbon::now()->subDays(2),
                'resolved_at'      => null,
            ]
        );

        Notification::updateOrCreate(
            ['user_id' => $customer2->id, 'shipment_id' => $shp2->id, 'type' => 'EXCEPTION_ALERT'],
            [
                'title'   => 'Adverse Weather Alert: TRC-2026-ASIA-NA-002',
                'message' => 'Vessel encountered Pacific storm conditions resulting in a 20-hour ETA delay.',
                'is_read' => false,
            ]
        );

        // ---------------------------------------------------------------------
        // 6. Scenario 3 (Corridor C): SEA -> AU (Jakarta -> Sydney)
        // DELIVERED, on-time (0h delay, NONE severity), within 24h threshold,
        // DOCUMENTATION_ISSUE resolved in 6 hours (populating resolution KPI)
        // ---------------------------------------------------------------------
        $shp3 = Shipment::updateOrCreate(['shipment_number' => 'TRC-2026-SEA-AU-003'], [
            'customer_id'             => $customer1->id,
            'carrier_id'              => $msc->id,
            'origin_location_id'      => $jkt->id,
            'destination_location_id' => $syd->id,
            'current_location_id'     => $syd->id,
            'cargo_type'              => 'Precision Medical Diagnostic Equipment',
            'total_weight_kg'         => 6200.00,
            'total_volume_cbm'        => 26.00,
            'current_status'          => 'DELIVERED',
            'expected_departure'      => Carbon::now()->subDays(6),
            'expected_arrival'        => Carbon::now()->subDay(),
            'estimated_arrival'       => Carbon::now()->subDay(),
            'is_delayed'              => false,
            'delay_hours'             => 0,
            'delay_severity'          => 'NONE',
            'is_sla_breached'         => false,
            'delay_reason'            => null,
        ]);

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shp3->id, 'sku' => 'SKU-MED-301'],
            [
                'item_name'  => 'Ultrasonic Diagnostic Scanners',
                'quantity'   => 20,
                'weight_kg'  => 3200.00,
                'volume_cbm' => 14.00,
            ]
        );

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shp3->id, 'sku' => 'SKU-MED-302'],
            [
                'item_name'  => 'Reagent Temperature Sensors',
                'quantity'   => 100,
                'weight_kg'  => 3000.00,
                'volume_cbm' => 12.00,
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shp3->id, 'event_status' => 'PICKED_UP', 'location_id' => $jkt->id],
            [
                'recorded_by'  => $operator->id,
                'latitude'     => $jkt->latitude,
                'longitude'    => $jkt->longitude,
                'description'  => 'Consignment picked up and staged at Tanjung Priok container terminal.',
                'is_exception' => false,
                'occurred_at'  => Carbon::now()->subDays(6),
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shp3->id, 'event_status' => 'IN_TRANSIT', 'location_id' => $jkt->id],
            [
                'recorded_by'  => $operator->id,
                'latitude'     => $jkt->latitude,
                'longitude'    => $jkt->longitude,
                'description'  => 'Maritime departure confirmed on southern corridor toward Sydney.',
                'is_exception' => false,
                'occurred_at'  => Carbon::now()->subDays(5),
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shp3->id, 'event_status' => 'IN_TRANSIT', 'location_id' => $syd->id],
            [
                'recorded_by'  => $operator->id,
                'latitude'     => $syd->latitude,
                'longitude'    => $syd->longitude,
                'description'  => 'Berthing and customs clearance inspection commenced at Port Botany Sydney.',
                'is_exception' => false,
                'occurred_at'  => Carbon::now()->subDays(2),
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shp3->id, 'event_status' => 'DELIVERED', 'location_id' => $syd->id],
            [
                'recorded_by'  => $operator->id,
                'latitude'     => $syd->latitude,
                'longitude'    => $syd->longitude,
                'description'  => 'Consignment successfully delivered and signed for at Botany Medical Depot.',
                'is_exception' => false,
                'occurred_at'  => Carbon::now()->subDay(),
            ]
        );

        ShipmentException::updateOrCreate(
            ['shipment_id' => $shp3->id, 'exception_type' => 'DOCUMENTATION_ISSUE'],
            [
                'reported_by'      => $operator->id,
                'severity'         => 'LOW',
                'status'           => 'RESOLVED',
                'description'      => 'Discrepancy detected in consignee phytosanitary import permit serial number.',
                'resolution_notes' => 'Electronic permit verified and cross-referenced with Australian biosecurity database.',
                'detected_at'      => Carbon::now()->subHours(36),
                'resolved_at'      => Carbon::now()->subHours(30), // Exactly 6 hours resolution time
            ]
        );

        // ---------------------------------------------------------------------
        // 7. Scenario 4: Global Demonstration Shipment (BOOKED -> ready for live transition)
        // ---------------------------------------------------------------------
        $shp4 = Shipment::updateOrCreate(['shipment_number' => 'TRC-2026-GLOBAL-004'], [
            'customer_id'             => $customer1->id,
            'carrier_id'              => $dhlAir->id,
            'origin_location_id'      => $sin->id,
            'destination_location_id' => $rtm->id,
            'current_location_id'     => $sin->id,
            'cargo_type'              => 'Automotive Lithium Battery Systems',
            'total_weight_kg'         => 14200.00,
            'total_volume_cbm'        => 55.00,
            'current_status'          => 'BOOKED',
            'expected_departure'      => Carbon::now()->addDay(),
            'expected_arrival'        => Carbon::now()->addDays(15),
            'estimated_arrival'       => Carbon::now()->addDays(15),
            'is_delayed'              => false,
            'delay_hours'             => 0,
            'delay_severity'          => 'NONE',
            'is_sla_breached'         => false,
            'delay_reason'            => null,
        ]);

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shp4->id, 'sku' => 'SKU-BAT-401'],
            [
                'item_name'  => 'Cell Module Battery Assemblies',
                'quantity'   => 120,
                'weight_kg'  => 14200.00,
                'volume_cbm' => 55.00,
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shp4->id, 'event_status' => 'BOOKED', 'location_id' => $sin->id],
            [
                'recorded_by'  => $operator->id,
                'latitude'     => $sin->latitude,
                'longitude'    => $sin->longitude,
                'description'  => 'Vessel container allocation and airway bill confirmed for scheduled departure.',
                'is_exception' => false,
                'occurred_at'  => Carbon::now()->subHours(4),
            ]
        );
    }
}
