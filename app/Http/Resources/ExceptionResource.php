<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExceptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'shipment_id'      => $this->shipment_id,
            'reported_by'      => $this->reported_by,
            'reporter'         => $this->whenLoaded('reporter', fn () => [
                'id'   => $this->reporter->id,
                'name' => $this->reporter->name,
                'role' => $this->reporter->role,
            ]),
            'exception_type'   => $this->exception_type,
            'severity'         => $this->severity,
            'status'           => $this->status,
            'description'      => $this->description,
            'resolution_notes' => $this->resolution_notes,
            'resolution_hours' => $this->resolution_hours !== null ? (float) $this->resolution_hours : null,
            'detected_at'      => $this->detected_at?->toIso8601String(),
            'resolved_at'      => $this->resolved_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
