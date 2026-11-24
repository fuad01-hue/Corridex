<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'shipment_number'         => $this->shipment_number,
            'customer_id'             => $this->customer_id,
            'customer'                => $this->whenLoaded('customer', fn () => [
                'id'    => $this->customer->id,
                'name'  => $this->customer->name,
                'email' => $this->customer->email,
                'role'  => $this->customer->role,
            ]),
            'carrier_id'              => $this->carrier_id,
            'carrier'                 => new CarrierResource($this->whenLoaded('carrier')),
            'origin_location_id'      => $this->origin_location_id,
            'origin'                  => new LocationResource($this->whenLoaded('origin')),
            'destination_location_id' => $this->destination_location_id,
            'destination'             => new LocationResource($this->whenLoaded('destination')),
            'current_location_id'     => $this->current_location_id,
            'current_location'        => new LocationResource($this->whenLoaded('currentLocation')),
            'cargo_type'              => $this->cargo_type,
            'total_weight_kg'         => (float) $this->total_weight_kg,
            'total_volume_cbm'        => (float) $this->total_volume_cbm,
            'current_status'          => $this->current_status,
            'is_delayed'              => (bool) $this->is_delayed,
            'delay_hours'             => (float) $this->delay_hours,
            'delay_severity'          => $this->delay_severity,
            'is_sla_breached'         => (bool) $this->is_sla_breached,
            'expected_departure'      => $this->expected_departure?->toIso8601String(),
            'expected_arrival'        => $this->expected_arrival?->toIso8601String(),
            'estimated_arrival'       => $this->estimated_arrival?->toIso8601String(),
            'actual_arrival'          => $this->actual_arrival?->toIso8601String(),
            'delay_reason'            => $this->delay_reason,

            // Formatted GeoJSON Coordinates for Leaflet GIS rendering
            'geo' => [
                'type' => 'FeatureCollection',
                'origin' => [
                    'name'        => $this->origin?->name,
                    'code'        => $this->origin?->code,
                    'country'     => $this->origin?->country,
                    'coordinates' => [(float) $this->origin?->latitude, (float) $this->origin?->longitude],
                ],
                'destination' => [
                    'name'        => $this->destination?->name,
                    'code'        => $this->destination?->code,
                    'country'     => $this->destination?->country,
                    'coordinates' => [(float) $this->destination?->latitude, (float) $this->destination?->longitude],
                ],
                'current' => [
                    'name'        => $this->currentLocation?->name ?? $this->origin?->name,
                    'coordinates' => [
                        (float) ($this->currentLocation?->latitude ?? $this->origin?->latitude),
                        (float) ($this->currentLocation?->longitude ?? $this->origin?->longitude),
                    ],
                ],
                'route_coordinates' => $this->buildRouteCoordinates(),
            ],

            'items'                   => ShipmentItemResource::collection($this->whenLoaded('items')),
            'tracking_events'         => TrackingEventResource::collection($this->whenLoaded('trackingEvents')),
            'exceptions'              => ExceptionResource::collection($this->whenLoaded('exceptions')),
            'created_at'              => $this->created_at?->toIso8601String(),
            'updated_at'              => $this->updated_at?->toIso8601String(),
        ];
    }

    private function buildRouteCoordinates(): array
    {
        $points = [];
        if ($this->origin) {
            $points[] = [(float) $this->origin->latitude, (float) $this->origin->longitude];
        }

        if ($this->relationLoaded('trackingEvents')) {
            foreach ($this->trackingEvents->sortBy('occurred_at') as $event) {
                if ($event->latitude && $event->longitude) {
                    $points[] = [(float) $event->latitude, (float) $event->longitude];
                }
            }
        }

        if ($this->destination) {
            $points[] = [(float) $this->destination->latitude, (float) $this->destination->longitude];
        }

        return $points;
    }
}
