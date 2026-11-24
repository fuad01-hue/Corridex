<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentService
{
    public function __construct(protected EtaService $etaService) {}

    /**
     * Create a new shipment along with manifest items, initial tracking event, and audit log.
     *
     * @param array $data
     * @param User $actor
     * @return Shipment
     */
    public function createShipment(array $data, User $actor): Shipment
    {
        return DB::transaction(function () use ($data, $actor) {
            $shipmentNumber = $data['shipment_number'] ?? $this->generateShipmentNumber();

            $expectedDeparture = Carbon::parse($data['expected_departure']);
            $expectedArrival = Carbon::parse($data['expected_arrival']);
            $estimatedArrival = isset($data['estimated_arrival'])
                ? Carbon::parse($data['estimated_arrival'])
                : $expectedArrival->copy();

            $originLocation = Location::findOrFail($data['origin_location_id']);

            $shipment = Shipment::create([
                'shipment_number'         => $shipmentNumber,
                'customer_id'             => $data['customer_id'],
                'carrier_id'              => $data['carrier_id'],
                'origin_location_id'      => $data['origin_location_id'],
                'destination_location_id' => $data['destination_location_id'],
                'current_location_id'     => $data['current_location_id'] ?? $data['origin_location_id'],
                'cargo_type'              => $data['cargo_type'],
                'total_weight_kg'         => $data['total_weight_kg'],
                'total_volume_cbm'        => $data['total_volume_cbm'],
                'expected_departure'      => $expectedDeparture,
                'expected_arrival'        => $expectedArrival,
                'estimated_arrival'       => $estimatedArrival,
                'current_status'          => 'CREATED',
                'is_delayed'              => false,
                'delay_hours'             => 0,
                'delay_severity'          => 'NONE',
                'is_sla_breached'         => false,
                'delay_reason'            => $data['delay_reason'] ?? null,
            ]);

            // Re-evaluate if initial ETA is later than expected arrival
            if ($estimatedArrival->gt($expectedArrival)) {
                $delayEval = $this->etaService->evaluateDelay($shipment, $estimatedArrival);
                $shipment->update([
                    'is_delayed'      => $delayEval['is_delayed'],
                    'delay_hours'     => $delayEval['delay_hours'],
                    'delay_severity'  => $delayEval['delay_severity'],
                    'is_sla_breached' => $delayEval['is_sla_breached'],
                ]);
            }

            // Create initial CREATED tracking event
            TrackingEvent::create([
                'shipment_id'  => $shipment->id,
                'location_id'  => $originLocation->id,
                'recorded_by'  => $actor->id,
                'event_status' => 'CREATED',
                'latitude'     => $originLocation->latitude,
                'longitude'    => $originLocation->longitude,
                'description'  => 'Shipment booking created and cargo manifest initialized.',
                'is_exception' => false,
                'occurred_at'  => now(),
            ]);

            // Create shipment items (manifest)
            if (! empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    ShipmentItem::create([
                        'shipment_id' => $shipment->id,
                        'sku'         => $item['sku'] ?? null,
                        'item_name'   => $item['item_name'],
                        'quantity'    => $item['quantity'] ?? 1,
                        'weight_kg'   => $item['weight_kg'] ?? 0,
                        'volume_cbm'  => $item['volume_cbm'] ?? 0,
                    ]);
                }
            } else {
                // Auto-generate primary manifest entry
                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'sku'         => 'SKU-' . strtoupper(Str::random(6)),
                    'item_name'   => $data['cargo_type'],
                    'quantity'    => 1,
                    'weight_kg'   => $data['total_weight_kg'],
                    'volume_cbm'  => $data['total_volume_cbm'],
                ]);
            }

            // Write to audit log
            AuditLog::create([
                'user_id'     => $actor->id,
                'action'      => 'CREATE_SHIPMENT',
                'entity_type' => 'Shipment',
                'entity_id'   => $shipment->id,
                'old_values'  => null,
                'new_values'  => $shipment->toArray(),
                'ip_address'  => request()?->ip() ?? '127.0.0.1',
                'created_at'  => now(),
            ]);

            return $shipment;
        });
    }

    /**
     * Generate unique shipment number in format TRC-2026-GLOBAL-XXX
     */
    public function generateShipmentNumber(): string
    {
        $year = date('Y');

        do {
            $latest = Shipment::where('shipment_number', 'LIKE', "TRC-{$year}-GLOBAL-%")->count() + 1;
            $candidate = sprintf('TRC-%s-GLOBAL-%03d', $year, $latest);

            if (! Shipment::where('shipment_number', $candidate)->exists()) {
                return $candidate;
            }

            // Fallback to random 3 alphanumeric characters if index exists
            $candidate = sprintf('TRC-%s-GLOBAL-%s', $year, strtoupper(Str::random(3)));
        } while (Shipment::where('shipment_number', $candidate)->exists());

        return $candidate;
    }
}
