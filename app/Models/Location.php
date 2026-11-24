<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function shipmentsAsOrigin(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_location_id');
    }

    public function shipmentsAsDestination(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_location_id');
    }

    public function shipmentsAsCurrent(): HasMany
    {
        return $this->hasMany(Shipment::class, 'current_location_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class);
    }
}
