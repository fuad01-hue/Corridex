<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExceptionResource;
use App\Http\Resources\ShipmentResource;
use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\EtaService;
use App\Services\ExternalWeatherService;
use App\Services\ShipmentService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Shipment::with(['origin', 'destination', 'carrier', 'customer', 'currentLocation']);

        if ($request->user()->role === 'customer') {
            $query->where('customer_id', $request->user()->id);
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'LIKE', "%{$search}%")
                  ->orWhere('cargo_type', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->query('status'));
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->query('carrier_id'));
        }

        if ($request->filled('origin_location_id')) {
            $query->where('origin_location_id', $request->query('origin_location_id'));
        }

        if ($request->filled('destination_location_id')) {
            $query->where('destination_location_id', $request->query('destination_location_id'));
        }

        if ($request->filled('delay_status')) {
            if ($request->query('delay_status') === 'delayed') {
                $query->where('is_delayed', true);
            } elseif ($request->query('delay_status') === 'ontime') {
                $query->where('is_delayed', false);
            }
        }

        $perPage = (int) $request->query('per_page', 15);
        $shipments = $query->latest()->paginate($perPage);

        return $this->successResponse(
            ShipmentResource::collection($shipments),
            'Shipments retrieved successfully.',
            200,
            [
                'current_page' => $shipments->currentPage(),
                'last_page'    => $shipments->lastPage(),
                'per_page'     => $shipments->perPage(),
                'total'        => $shipments->total(),
            ]
        );
    }

    public function store(Request $request, ShipmentService $shipmentService): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'             => 'required|exists:users,id',
            'carrier_id'              => 'required|exists:carriers,id',
            'origin_location_id'      => 'required|exists:locations,id|different:destination_location_id',
            'destination_location_id' => 'required|exists:locations,id',
            'current_location_id'     => 'nullable|exists:locations,id',
            'latitude'                => 'nullable|numeric|between:-90,90',
            'longitude'               => 'nullable|numeric|between:-180,180',
            'cargo_type'              => 'required|string|max:150',
            'total_weight_kg'         => 'required|numeric|min:1',
            'total_volume_cbm'        => 'required|numeric|min:0.1',
            'expected_departure'      => 'required|date',
            'expected_arrival'        => 'required|date|after:expected_departure',
            'estimated_arrival'       => 'nullable|date',
            'items'                   => 'nullable|array',
            'items.*.item_name'       => 'required_with:items|string',
            'items.*.sku'             => 'nullable|string',
            'items.*.quantity'        => 'nullable|integer|min:1',
            'items.*.weight_kg'       => 'nullable|numeric|min:0',
            'items.*.volume_cbm'      => 'nullable|numeric|min:0',
        ]);

        $shipment = $shipmentService->createShipment($validated, $request->user());

        return $this->successResponse(
            new ShipmentResource($shipment->load(['origin', 'destination', 'carrier', 'customer', 'items', 'trackingEvents'])),
            'Shipment booking created successfully.',
            201
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::with([
            'customer',
            'carrier',
            'origin',
            'destination',
            'currentLocation',
            'items',
            'trackingEvents.location',
            'trackingEvents.recorder',
            'exceptions.reporter',
        ])->findOrFail($id);

        if ($request->user()->role === 'customer' && $shipment->customer_id !== $request->user()->id) {
            return $this->errorResponse(
                'Forbidden. You do not have permission to view this consignment.',
                ['customer_id' => 'Cross-tenant access restricted.'],
                'FORBIDDEN',
                403
            );
        }

        return $this->successResponse(
            new ShipmentResource($shipment),
            'Consignment retrieved successfully.'
        );
    }

    public function update(Request $request, int $id, EtaService $etaService): JsonResponse
    {
        $shipment = Shipment::findOrFail($id);

        $validated = $request->validate([
            'cargo_type'          => 'nullable|string|max:150',
            'current_location_id' => 'nullable|exists:locations,id',
            'estimated_arrival'   => 'nullable|date',
            'delay_reason'        => 'nullable|string|max:255',
            'total_weight_kg'     => 'nullable|numeric|min:1',
            'total_volume_cbm'    => 'nullable|numeric|min:0.1',
        ]);

        $oldValues = $shipment->toArray();
        $updateData = $validated;

        if (! empty($validated['estimated_arrival'])) {
            $newEta = Carbon::parse($validated['estimated_arrival']);
            $delayEval = $etaService->evaluateDelay($shipment, $newEta);

            $updateData['is_delayed']      = $delayEval['is_delayed'];
            $updateData['delay_hours']     = $delayEval['delay_hours'];
            $updateData['delay_severity']  = $delayEval['delay_severity'];
            $updateData['is_sla_breached'] = $delayEval['is_sla_breached'];
        }

        $shipment->update($updateData);

        AuditLog::create([
            'user_id'     => $request->user()->id,
            'action'      => 'UPDATE_SHIPMENT_API',
            'entity_type' => 'Shipment',
            'entity_id'   => $shipment->id,
            'old_values'  => $oldValues,
            'new_values'  => $shipment->fresh()->toArray(),
            'ip_address'  => $request->ip() ?? '127.0.0.1',
            'created_at'  => now(),
        ]);

        return $this->successResponse(
            new ShipmentResource($shipment->fresh()->load(['origin', 'destination', 'carrier', 'customer', 'currentLocation'])),
            'Shipment updated successfully.'
        );
    }

    /**
     * GET /api/shipments/{id}/route
     * Returns ordered GIS waypoints and point-in-time maritime telemetry.
     */
    public function route(Request $request, int $id, ExternalWeatherService $weatherService): JsonResponse
    {
        $shipment = Shipment::with([
            'origin',
            'destination',
            'currentLocation',
            'trackingEvents.location',
            'exceptions',
        ])->findOrFail($id);

        if ($request->user()->role === 'customer' && $shipment->customer_id !== $request->user()->id) {
            return $this->errorResponse(
                'Forbidden. You do not have permission to view route telemetry for this consignment.',
                [],
                'FORBIDDEN',
                403
            );
        }

        $currentHub = $shipment->currentLocation ?? $shipment->origin;
        $weather = null;
        if ($currentHub && $currentHub->latitude && $currentHub->longitude) {
            $weather = $weatherService->getCurrentWeather((float) $currentHub->latitude, (float) $currentHub->longitude);
        }

        $waypoints = [];

        // 1. Origin Waypoint
        if ($shipment->origin) {
            $waypoints[] = [
                'type'        => 'ORIGIN',
                'label'       => $shipment->origin->name,
                'code'        => $shipment->origin->code,
                'country'     => $shipment->origin->country,
                'lat'         => (float) $shipment->origin->latitude,
                'lng'         => (float) $shipment->origin->longitude,
                'coordinates' => [(float) $shipment->origin->latitude, (float) $shipment->origin->longitude],
                'status'      => 'ORIGIN',
                'time'        => $shipment->expected_departure?->toIso8601String(),
            ];
        }

        // 2. Intermediate Waypoints
        foreach ($shipment->trackingEvents->sortBy('occurred_at') as $event) {
            if ($event->latitude && $event->longitude) {
                $waypoints[] = [
                    'type'         => 'WAYPOINT',
                    'label'        => $event->location?->name ?? 'Maritime Navigation Track',
                    'code'         => $event->location?->code ?? 'WAYPOINT',
                    'country'      => $event->location?->country ?? 'International Waters',
                    'lat'          => (float) $event->latitude,
                    'lng'          => (float) $event->longitude,
                    'coordinates'  => [(float) $event->latitude, (float) $event->longitude],
                    'status'       => $event->event_status,
                    'is_exception' => (bool) $event->is_exception,
                    'time'         => $event->occurred_at?->toIso8601String(),
                    'description'  => $event->description,
                ];
            }
        }

        // 3. Destination Waypoint
        if ($shipment->destination) {
            $waypoints[] = [
                'type'        => 'DESTINATION',
                'label'       => $shipment->destination->name,
                'code'        => $shipment->destination->code,
                'country'     => $shipment->destination->country,
                'lat'         => (float) $shipment->destination->latitude,
                'lng'         => (float) $shipment->destination->longitude,
                'coordinates' => [(float) $shipment->destination->latitude, (float) $shipment->destination->longitude],
                'status'      => $shipment->current_status === 'DELIVERED' ? 'DELIVERED' : 'EXPECTED',
                'time'        => ($shipment->estimated_arrival ?? $shipment->expected_arrival)?->toIso8601String(),
            ];
        }

        $routeData = [
            'shipment_id'        => $shipment->id,
            'shipment_number'    => $shipment->shipment_number,
            'current_status'     => $shipment->current_status,
            'is_delayed'         => (bool) $shipment->is_delayed,
            'delay_hours'        => (float) $shipment->delay_hours,
            'delay_severity'     => $shipment->delay_severity,
            'is_sla_breached'    => (bool) $shipment->is_sla_breached,
            'corridor'           => ($shipment->origin?->region ?? 'SEA') . ' -> ' . ($shipment->destination?->region ?? 'GLOBAL'),
            'waypoints'          => $waypoints,
            'active_exceptions'  => ExceptionResource::collection($shipment->exceptions->where('status', '!==', 'RESOLVED')),
            'telemetry'          => [
                'hub_name'       => $currentHub?->name,
                'hub_code'       => $currentHub?->code,
                'status'         => ($weather['available'] ?? false) ? 'LIVE' : 'OFFLINE',
                'temperature_c'  => $weather['temperature'] ?? null,
                'windspeed_kmh'  => $weather['windspeed'] ?? null,
                'weathercode'    => $weather['weathercode'] ?? null,
                'available'      => $weather['available'] ?? false,
                'disclaimer'     => ExternalWeatherService::OPERATIONAL_DISCLAIMER,
            ],
        ];

        return $this->successResponse($routeData, 'Maritime route waypoints and telemetry retrieved successfully.');
    }

    /**
     * GET /api/analytics
     * Operational analytics KPI aggregation (Admin and Operator).
     */
    public function analytics(Request $request): JsonResponse
    {
        $baseQuery = Shipment::query();

        $totalShipments = (clone $baseQuery)->count();
        $inTransit      = (clone $baseQuery)->where('current_status', 'IN_TRANSIT')->count();
        $delayed        = (clone $baseQuery)->where('is_delayed', true)->count();
        $slaBreached    = (clone $baseQuery)->where('is_sla_breached', true)->count();
        $delivered      = (clone $baseQuery)->where('current_status', 'DELIVERED')->count();
        $openExceptions = ShipmentException::whereIn('status', ['OPEN', 'INVESTIGATING'])->count();

        // Corridor breakdown
        $seaEuTotal = (clone $baseQuery)->where('shipment_number', 'LIKE', '%SEA-EU%')->count();
        $seaEuOnTime = (clone $baseQuery)->where('shipment_number', 'LIKE', '%SEA-EU%')->where('is_delayed', false)->count();

        $asiaNaTotal = (clone $baseQuery)->where('shipment_number', 'LIKE', '%ASIA-NA%')->count();
        $asiaNaDelayed = (clone $baseQuery)->where('shipment_number', 'LIKE', '%ASIA-NA%')->where('is_delayed', true)->count();

        $seaAuTotal = (clone $baseQuery)->where('shipment_number', 'LIKE', '%SEA-AU%')->count();

        return $this->successResponse([
            'metrics' => [
                'total_shipments'   => $totalShipments,
                'in_transit'        => $inTransit,
                'delayed'           => $delayed,
                'sla_breached'      => $slaBreached,
                'delivered'         => $delivered,
                'open_exceptions'   => $openExceptions,
            ],
            'corridors' => [
                'SEA-EU' => [
                    'sla_threshold_hours' => config('cargotrace.corridor_thresholds.SEA-EU', 12),
                    'total'               => $seaEuTotal,
                    'on_time_rate_pct'    => $seaEuTotal > 0 ? round(($seaEuOnTime / $seaEuTotal) * 100, 1) : 100.0,
                ],
                'ASIA-NA' => [
                    'sla_threshold_hours' => config('cargotrace.corridor_thresholds.ASIA-NA', 18),
                    'total'               => $asiaNaTotal,
                    'delayed_count'       => $asiaNaDelayed,
                ],
                'SEA-AU' => [
                    'sla_threshold_hours' => config('cargotrace.corridor_thresholds.SEA-AU', 24),
                    'total'               => $seaAuTotal,
                ],
            ],
        ], 'System analytics retrieved successfully.');
    }

    /**
     * GET /api/audit-logs
     * Access immutable audit trail records (Admin only).
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $logs = AuditLog::with('user')->latest()->paginate($perPage);

        return $this->successResponse(
            $logs->items(),
            'Audit logs retrieved successfully.',
            200,
            [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
            ]
        );
    }
}
