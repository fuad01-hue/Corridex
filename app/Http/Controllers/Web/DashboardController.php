<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $baseQuery = Shipment::query();

        // Scope to customer's own shipments if user role is customer
        if ($user->role === 'customer') {
            $baseQuery->where('customer_id', $user->id);
        }

        // Global KPI Cards
        $totalShipments = (clone $baseQuery)->count();
        $inTransit      = (clone $baseQuery)->where('current_status', 'IN_TRANSIT')->count();
        $delayed        = (clone $baseQuery)->where('is_delayed', true)->count();

        $exceptionQuery = ShipmentException::query();
        if ($user->role === 'customer') {
            $exceptionQuery->whereHas('shipment', fn ($q) => $q->where('customer_id', $user->id));
        }
        $openExceptions = (clone $exceptionQuery)->whereIn('status', ['OPEN', 'INVESTIGATING'])->count();

        // ---------------------------------------------------------------------
        // Corridor KPI 1: Corridor SEA -> EU (12h SLA) - On-Time Delivery Rate (%)
        // ---------------------------------------------------------------------
        $corridorSeaEuQuery = (clone $baseQuery)->where(function ($q) {
            $q->where('shipment_number', 'LIKE', '%SEA-EU%')
              ->orWhere(function ($sub) {
                  $sub->whereHas('origin', fn ($loc) => $loc->where('region', 'Southeast Asia'))
                      ->whereHas('destination', fn ($loc) => $loc->where('region', 'Europe'));
              });
        });
        $seaEuTotal = (clone $corridorSeaEuQuery)->count();
        $seaEuOnTime = (clone $corridorSeaEuQuery)->where('is_delayed', false)->count();
        $seaEuOnTimeRate = $seaEuTotal > 0 ? round(($seaEuOnTime / $seaEuTotal) * 100, 1) : 100.0;

        // ---------------------------------------------------------------------
        // Corridor KPI 2: Corridor Asia -> NA (18h SLA) - Average Delay Duration (Hours)
        // ---------------------------------------------------------------------
        $corridorAsiaNaQuery = (clone $baseQuery)->where(function ($q) {
            $q->where('shipment_number', 'LIKE', '%ASIA-NA%')
              ->orWhere(function ($sub) {
                  $sub->whereHas('origin', fn ($loc) => $loc->where('region', 'LIKE', '%Asia%'))
                      ->whereHas('destination', fn ($loc) => $loc->where('region', 'North America'));
              });
        });
        $asiaNaDelayed = (clone $corridorAsiaNaQuery)->where('is_delayed', true);
        $asiaNaAvgDelay = round((float) ($asiaNaDelayed->avg('delay_hours') ?? 0), 1);

        // ---------------------------------------------------------------------
        // Corridor KPI 3: Corridor SEA -> AU (24h SLA) - Average Exception Resolution Time (Hours)
        // ---------------------------------------------------------------------
        $seaAuExceptions = (clone $exceptionQuery)
            ->where('status', 'RESOLVED')
            ->whereNotNull('resolved_at')
            ->whereHas('shipment', function ($q) {
                $q->where('shipment_number', 'LIKE', '%SEA-AU%')
                  ->orWhere(function ($sub) {
                      $sub->whereHas('origin', fn ($loc) => $loc->where('region', 'Southeast Asia'))
                          ->whereHas('destination', fn ($loc) => $loc->whereIn('region', ['Australia', 'Oceania']));
                  });
            })
            ->get();

        $seaAuAvgResolution = 0.0;
        if ($seaAuExceptions->count() > 0) {
            $totalHours = $seaAuExceptions->sum(fn ($exc) => $exc->detected_at->diffInHours($exc->resolved_at));
            $seaAuAvgResolution = round($totalHours / $seaAuExceptions->count(), 1);
        }

        // ---------------------------------------------------------------------
        // Chart 1: Shipment Lifecycle Status Distribution
        // ---------------------------------------------------------------------
        $statusBreakdown = [
            'CREATED'    => (clone $baseQuery)->where('current_status', 'CREATED')->count(),
            'BOOKED'     => (clone $baseQuery)->where('current_status', 'BOOKED')->count(),
            'PICKED_UP'  => (clone $baseQuery)->where('current_status', 'PICKED_UP')->count(),
            'IN_TRANSIT' => $inTransit,
            'DELIVERED'  => (clone $baseQuery)->where('current_status', 'DELIVERED')->count(),
        ];

        // ---------------------------------------------------------------------
        // Chart 2: Carrier Volume Breakdown
        // ---------------------------------------------------------------------
        $carrierData = Carrier::withCount(['shipments' => function ($q) use ($user) {
            if ($user->role === 'customer') {
                $q->where('customer_id', $user->id);
            }
        }])->get();

        $carrierVolumeBreakdown = [
            'labels' => $carrierData->pluck('name')->toArray(),
            'counts' => $carrierData->pluck('shipments_count')->toArray(),
        ];

        // Fetch all shipments for radar map with full relations
        $allMapShipments = (clone $baseQuery)
            ->with(['origin', 'destination', 'carrier', 'customer', 'currentLocation', 'trackingEvents.location', 'exceptions', 'items'])
            ->latest()
            ->get();

        $recentShipments = $allMapShipments->take(8);

        // Compile GIS route corridors for the dashboard radar map
        $dashboardMapRoutes = [];
        foreach ($allMapShipments as $s) {
            if ($s->origin && $s->destination) {
                $curLat = (float) ($s->currentLocation?->latitude ?? $s->origin->latitude);
                $curLng = (float) ($s->currentLocation?->longitude ?? $s->origin->longitude);
                $destLat = (float) $s->destination->latitude;
                $destLng = (float) $s->destination->longitude;
                $origLat = (float) $s->origin->latitude;
                $origLng = (float) $s->origin->longitude;

                // Determine bearing/heading for directional arrow
                $heading = $this->calculateBearing($curLat, $curLng, $destLat, $destLng);
                if ($heading == 0.0 && ($curLat != $origLat || $curLng != $origLng)) {
                    $heading = $this->calculateBearing($origLat, $origLng, $curLat, $curLng);
                }

                // Calculate journey progress percentage
                $totalDist = $this->calculateDistance($origLat, $origLng, $destLat, $destLng);
                $coveredDist = $this->calculateDistance($origLat, $origLng, $curLat, $curLng);
                $progressPercent = $totalDist > 0 ? min(98, max(5, round(($coveredDist / $totalDist) * 100))) : 50;
                if ($s->current_status === 'DELIVERED') {
                    $progressPercent = 100;
                }

                // Compile detailed waypoint sequence
                $waypoints = [];
                $waypoints[] = [
                    'name'     => $s->origin->name,
                    'code'     => $s->origin->code,
                    'city'     => $s->origin->city,
                    'country'  => $s->origin->country,
                    'lat'      => $origLat,
                    'lng'      => $origLng,
                    'type'     => 'ORIGIN',
                    'status'   => 'DEPARTED',
                    'time'     => $s->expected_departure?->format('d M, H:i') ?? 'Departed',
                ];

                foreach ($s->trackingEvents as $evt) {
                    $eLat = (float) ($evt->latitude ?? $evt->location?->latitude ?? 0);
                    $eLng = (float) ($evt->longitude ?? $evt->location?->longitude ?? 0);
                    if ($eLat != 0.0 && $eLng != 0.0) {
                        $waypoints[] = [
                            'name'         => $evt->location?->name ?? 'Waypoint',
                            'code'         => $evt->location?->code ?? '',
                            'city'         => $evt->location?->city ?? '',
                            'country'      => $evt->location?->country ?? '',
                            'lat'          => $eLat,
                            'lng'          => $eLng,
                            'type'         => 'WAYPOINT',
                            'status'       => $evt->status,
                            'description'  => $evt->description,
                            'time'         => $evt->occurred_at?->format('d M, H:i') ?? '',
                            'is_exception' => (bool) $evt->is_exception,
                        ];
                    }
                }

                $waypoints[] = [
                    'name'     => $s->destination->name,
                    'code'     => $s->destination->code,
                    'city'     => $s->destination->city,
                    'country'  => $s->destination->country,
                    'lat'      => $destLat,
                    'lng'      => $destLng,
                    'type'     => 'DESTINATION',
                    'status'   => $s->current_status === 'DELIVERED' ? 'DELIVERED' : 'PENDING_ARRIVAL',
                    'time'     => $s->estimated_arrival?->format('d M, H:i') ?? ($s->expected_arrival?->format('d M, H:i') ?? 'Estimated'),
                ];

                // Active exceptions
                $activeExceptions = $s->exceptions->whereIn('status', ['OPEN', 'INVESTIGATING'])->map(function ($exc) {
                    return [
                        'id'          => $exc->id,
                        'type'        => $exc->exception_type,
                        'severity'    => $exc->severity,
                        'status'      => $exc->status,
                        'description' => $exc->description,
                        'detected_at' => $exc->detected_at?->format('d M Y, H:i'),
                    ];
                })->values()->toArray();

                $dashboardMapRoutes[] = [
                    'id'                 => $s->id,
                    'number'             => $s->shipment_number,
                    'status'             => $s->current_status,
                    'is_delayed'         => (bool) $s->is_delayed,
                    'delay_hours'        => (int) ($s->delay_hours ?? 0),
                    'delay_severity'     => $s->delay_severity,
                    'delay_reason'       => $s->delay_reason,
                    'is_sla_breached'    => (bool) $s->is_sla_breached,
                    'cargo_type'         => $s->cargo_type,
                    'total_weight_kg'    => (float) $s->total_weight_kg,
                    'total_volume_cbm'   => (float) $s->total_volume_cbm,
                    'customer_name'      => $s->customer?->name ?? 'Enterprise Client',
                    'expected_departure' => $s->expected_departure?->format('d M Y, H:i') ?? 'N/A',
                    'expected_arrival'   => $s->expected_arrival?->format('d M Y, H:i') ?? 'N/A',
                    'estimated_arrival'  => $s->estimated_arrival?->format('d M Y, H:i') ?? 'N/A',
                    'carrier'            => [
                        'name'           => $s->carrier?->name ?? 'Maritime Freight Carrier',
                        'code'           => $s->carrier?->code ?? 'N/A',
                        'transport_mode' => $s->carrier?->transport_mode ?? 'SEA',
                    ],
                    'origin'             => [
                        'name'    => $s->origin->name,
                        'code'    => $s->origin->code,
                        'city'    => $s->origin->city,
                        'country' => $s->origin->country,
                        'lat'     => $origLat,
                        'lng'     => $origLng,
                    ],
                    'destination'        => [
                        'name'    => $s->destination->name,
                        'code'    => $s->destination->code,
                        'city'    => $s->destination->city,
                        'country' => $s->destination->country,
                        'lat'     => $destLat,
                        'lng'     => $destLng,
                    ],
                    'current'            => [
                        'name'    => $s->currentLocation?->name ?? $s->origin->name,
                        'city'    => $s->currentLocation?->city ?? $s->origin->city,
                        'country' => $s->currentLocation?->country ?? $s->origin->country,
                        'lat'     => $curLat,
                        'lng'     => $curLng,
                    ],
                    'heading'            => $heading,
                    'progress_percent'   => $progressPercent,
                    'waypoints'          => $waypoints,
                    'active_exceptions'  => $activeExceptions,
                    'items_count'        => $s->items->count(),
                    'items_summary'      => $s->items->take(2)->pluck('description')->join(', '),
                ];
            }
        }

        return view('dashboard.index', compact(
            'totalShipments',
            'inTransit',
            'delayed',
            'openExceptions',
            'seaEuOnTimeRate',
            'asiaNaAvgDelay',
            'seaAuAvgResolution',
            'statusBreakdown',
            'carrierVolumeBreakdown',
            'recentShipments',
            'dashboardMapRoutes'
        ));
    }

    /**
     * Compute forward azimuth (bearing) from Point A to Point B in degrees (0 - 360).
     */
    private function calculateBearing(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        if (abs($lat1 - $lat2) < 0.0001 && abs($lon1 - $lon2) < 0.0001) {
            return 0.0;
        }

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $dLon = deg2rad($lon2 - $lon1);

        $y = sin($dLon) * cos($lat2Rad);
        $x = cos($lat1Rad) * sin($lat2Rad) - sin($lat1Rad) * cos($lat2Rad) * cos($dLon);
        $bearing = atan2($y, $x);

        return round(fmod(rad2deg($bearing) + 360, 360), 1);
    }

    /**
     * Compute Great-Circle distance between two coordinates in kilometers.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
