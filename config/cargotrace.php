<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SLA Breach Threshold (Hours)
    |--------------------------------------------------------------------------
    | Default SLA breach threshold in hours when corridor-specific threshold
    | is not explicitly provided.
    */
    'sla_breach_threshold_hours' => (int) env('CARGOTRACE_SLA_THRESHOLD_HOURS', 12),

    /*
    |--------------------------------------------------------------------------
    | Global Corridor SLA Thresholds (Hours)
    |--------------------------------------------------------------------------
    | Specific delivery delay thresholds for designated international shipping
    | corridors.
    */
    'corridor_thresholds' => [
        'SEA-EU'  => 12, // Southeast Asia -> Europe
        'ASIA-NA' => 18, // Asia -> North America
        'SEA-AU'  => 24, // Southeast Asia -> Australia
    ],

    /*
    |--------------------------------------------------------------------------
    | Delay Severity Thresholds (Hours)
    |--------------------------------------------------------------------------
    | Deterministic categorization of delay hours:
    | LOW: 1 to 6 hours
    | MEDIUM: 7 to 24 hours
    | HIGH: Greater than 24 hours
    */
    'delay_severity_thresholds' => [
        'low'    => 6,   // 1 - 6 hours: LOW
        'medium' => 24,  // 7 - 24 hours: MEDIUM (> 24 hours: HIGH)
    ],

    /*
    |--------------------------------------------------------------------------
    | Valid Lifecycle Status Transitions
    |--------------------------------------------------------------------------
    | State machine definition for shipment progression.
    | DELIVERED is terminal and cannot transition to earlier states.
    */
    'valid_transitions' => [
        'CREATED'    => ['BOOKED'],
        'BOOKED'     => ['PICKED_UP'],
        'PICKED_UP'  => ['IN_TRANSIT'],
        'IN_TRANSIT' => ['IN_TRANSIT', 'DELIVERED'],
        'DELIVERED'  => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Standard Shipment Exception Types
    |--------------------------------------------------------------------------
    | Supported logistical operational exception categories.
    */
    'standard_exception_types' => [
        'CUSTOMS_HOLD',
        'WEATHER_DELAY',
        'PORT_CONGESTION',
        'DAMAGED',
        'MISROUTED',
        'DOCUMENTATION_ISSUE',
    ],

    /*
    |--------------------------------------------------------------------------
    | External Weather API Configuration
    |--------------------------------------------------------------------------
    | Open-Meteo telemetry integration endpoints and HTTP timeouts.
    */
    'weather_api' => [
        'base_url'        => env('WEATHER_API_URL', 'https://api.open-meteo.com/v1/forecast'),
        'timeout_seconds' => (int) env('WEATHER_API_TIMEOUT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Interactive Map Provider & API Key Configuration
    |--------------------------------------------------------------------------
    | Supports Esri Maritime Canvas (100% free, watermark-free default),
    | Mapbox GL, MapTiler, or CartoDB (requires CARTO_API_KEY).
    | When MAPBOX_API_KEY (or MAP_API_KEY) is provided, high-definition Mapbox
    | vector/raster tiles are loaded. If omitted or blank, Leaflet falls back
    | automatically to Esri World Dark/Light Gray Canvas with zero watermarks.
    */
    'map' => [
        'provider'     => env('MAP_PROVIDER', 'esri'),
        'mapbox_key'   => env('MAPBOX_API_KEY', env('MAP_API_KEY', '')),
        'maptiler_key' => env('MAPTILER_API_KEY', ''),
        'carto_key'    => env('CARTO_API_KEY', ''),
    ],
];
