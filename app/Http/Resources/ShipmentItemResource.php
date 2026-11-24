<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'shipment_id' => $this->shipment_id,
            'sku'         => $this->sku,
            'item_name'   => $this->item_name,
            'quantity'    => (int) $this->quantity,
            'weight_kg'   => (float) $this->weight_kg,
            'volume_cbm'  => (float) $this->volume_cbm,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
