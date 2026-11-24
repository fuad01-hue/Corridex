<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'shipment_id'  => $this->shipment_id,
            'location_id'  => $this->location_id,
            'location'     => new LocationResource($this->whenLoaded('location')),
            'recorded_by'  => $this->recorded_by,
            'recorder'     => $this->whenLoaded('recorder', fn () => [
                'id'   => $this->recorder->id,
                'name' => $this->recorder->name,
                'role' => $this->recorder->role,
            ]),
            'event_status' => $this->event_status,
            'is_exception' => (bool) $this->is_exception,
            'latitude'     => (float) $this->latitude,
            'longitude'    => (float) $this->longitude,
            'coordinates'  => [(float) $this->latitude, (float) $this->longitude],
            'description'  => $this->description,
            'occurred_at'  => $this->occurred_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
