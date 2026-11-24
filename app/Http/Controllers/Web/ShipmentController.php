<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use App\Services\ExceptionService;
use App\Services\ExternalWeatherService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::with(['origin', 'destination', 'carrier', 'customer']);

        // Data scoping: Customer role can only view their own consignments
        if ($request->user()->role === 'customer') {
            $query->where('customer_id', $request->user()->id);
        }

        // Search by shipment number or cargo type
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'LIKE', "%{$search}%")
                  ->orWhere('cargo_type', 'LIKE', "%{$search}%");
            });
        }

        // Filter by lifecycle status
        if ($request->filled('status')) {
            $query->where('current_status', $request->query('status'));
        }

        // Filter by carrier
        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->query('carrier_id'));
        }

        // Filter by origin port
        if ($request->filled('origin_location_id')) {
            $query->where('origin_location_id', $request->query('origin_location_id'));
        }

        // Filter by destination port
        if ($request->filled('destination_location_id')) {
            $query->where('destination_location_id', $request->query('destination_location_id'));
        }

        // Filter by delay status
        if ($request->filled('delay_status')) {
            if ($request->query('delay_status') === 'delayed') {
                $query->where('is_delayed', true);
            } elseif ($request->query('delay_status') === 'ontime') {
                $query->where('is_delayed', false);
            }
        }

        $shipments = $query->latest()->paginate(10)->withQueryString();
        $carriers  = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return view('shipments.index', compact('shipments', 'carriers', 'locations', 'customers'));
    }

    public function store(Request $request, ShipmentService $shipmentService)
    {
        if (! in_array($request->user()->role, ['admin', 'operator'], true)) {
            abort(403, 'Forbidden. Only administrators and operators can book new shipments.');
        }

        $validated = $request->validate([
            'customer_id'             => 'required|exists:users,id',
            'carrier_id'              => 'required|exists:carriers,id',
            'origin_location_id'      => 'required|exists:locations,id|different:destination_location_id',
            'destination_location_id' => 'required|exists:locations,id',
            'cargo_type'              => 'required|string|max:150',
            'total_weight_kg'         => 'required|numeric|min:1',
            'total_volume_cbm'        => 'required|numeric|min:0.1',
            'expected_departure'      => 'required|date',
            'expected_arrival'        => 'required|date|after:expected_departure',
            'estimated_arrival'       => 'nullable|date',
        ]);

        $shipment = $shipmentService->createShipment($validated, $request->user());

        return redirect()->route('shipments.show', $shipment->id)
            ->with('success', "Shipment {$shipment->shipment_number} was successfully created.");
    }

    public function show(Request $request, int $id, ExternalWeatherService $weatherService)
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

        // Strict authorization scoping: Customer cannot view another customer's shipment
        if ($request->user()->role === 'customer' && $shipment->customer_id !== $request->user()->id) {
            abort(403, 'Forbidden. You do not have authorization to view this consignment.');
        }

        // Compile chronological Leaflet route points with rich GIS metadata
        $mapPoints = [];
        $isOriginCurrent = in_array($shipment->current_status, ['CREATED', 'BOOKED'], true);

        // 1. Origin Port
        $mapPoints[] = [
            'type'         => 'ORIGIN',
            'lat'          => (float) $shipment->origin->latitude,
            'lng'          => (float) $shipment->origin->longitude,
            'label'        => $shipment->origin->name,
            'code'         => $shipment->origin->code,
            'country'      => $shipment->origin->country,
            'status'       => 'ORIGIN PORT',
            'time'         => $shipment->expected_departure?->format('d M Y H:i'),
            'is_exception' => false,
            'is_current'   => $isOriginCurrent,
        ];

        // 2. Tracking Waypoints
        $events = $shipment->trackingEvents->sortBy('occurred_at');
        $eventCount = $events->count();
        $idx = 0;
        foreach ($events as $event) {
            $idx++;
            $isLatestWaypoint = ($idx === $eventCount && $shipment->current_status !== 'DELIVERED');
            $mapPoints[] = [
                'type'         => 'WAYPOINT',
                'lat'          => (float) $event->latitude,
                'lng'          => (float) $event->longitude,
                'label'        => $event->location?->name ?? 'Maritime Navigation Track',
                'code'         => $event->location?->code ?? 'WAYPOINT',
                'country'      => $event->location?->country ?? 'International Waters',
                'status'       => $event->event_status,
                'time'         => $event->occurred_at?->format('d M Y H:i'),
                'is_exception' => (bool) $event->is_exception,
                'is_current'   => $isLatestWaypoint,
                'description'  => $event->description,
            ];
        }

        // 3. Destination Port
        $isDestCurrent = ($shipment->current_status === 'DELIVERED');
        $mapPoints[] = [
            'type'         => 'DESTINATION',
            'lat'          => (float) $shipment->destination->latitude,
            'lng'          => (float) $shipment->destination->longitude,
            'label'        => $shipment->destination->name,
            'code'         => $shipment->destination->code,
            'country'      => $shipment->destination->country,
            'status'       => $isDestCurrent ? 'DELIVERED' : 'DESTINATION PORT',
            'time'         => ($shipment->estimated_arrival ?? $shipment->expected_arrival)?->format('d M Y H:i'),
            'is_exception' => false,
            'is_current'   => $isDestCurrent,
        ];

        // Fetch live hub weather telemetry
        $currentLat = $shipment->currentLocation?->latitude ?? $shipment->origin->latitude;
        $currentLng = $shipment->currentLocation?->longitude ?? $shipment->origin->longitude;
        $weather    = $weatherService->getCurrentWeather((float) $currentLat, (float) $currentLng);

        $timeline  = $shipment->trackingEvents()->orderByDesc('occurred_at')->get();
        $locations = Location::orderBy('name')->get();
        $allowedNextStatuses = config("cargotrace.valid_transitions.{$shipment->current_status}", []);

        return view('shipments.show', compact(
            'shipment',
            'mapPoints',
            'weather',
            'timeline',
            'locations',
            'allowedNextStatuses'
        ));
    }

    public function appendTracking(Request $request, int $id, TrackingService $trackingService)
    {
        if (! in_array($request->user()->role, ['admin', 'operator'], true)) {
            abort(403, 'Forbidden. Only administrators and logistics operators can record tracking events.');
        }

        $shipment = Shipment::findOrFail($id);

        $validated = $request->validate([
            'event_status'      => 'required|string|in:CREATED,BOOKED,PICKED_UP,IN_TRANSIT,DELIVERED',
            'location_id'       => 'nullable|exists:locations,id',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
            'description'       => 'required|string|max:500',
            'occurred_at'       => 'nullable|date',
            'estimated_arrival' => 'nullable|date',
            'delay_reason'      => 'nullable|string|max:255',
            'is_exception'      => 'nullable|boolean',
        ]);

        try {
            $trackingService->appendEvent($shipment, $validated, $request->user());
            return back()->with('success', 'Tracking event appended and ETA delay metrics updated successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['event_status' => $e->getMessage()]);
        }
    }

    public function storeException(Request $request, int $id, ExceptionService $exceptionService)
    {
        if (! in_array($request->user()->role, ['admin', 'operator'], true)) {
            abort(403, 'Forbidden. Only administrators and logistics operators can report shipment exceptions.');
        }

        $shipment = Shipment::findOrFail($id);

        $validated = $request->validate([
            'exception_type'   => 'required|in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE',
            'severity'         => 'required|in:LOW,MEDIUM,HIGH',
            'description'      => 'required|string|max:500',
            'resolution_notes' => 'nullable|string|max:500',
            'detected_at'      => 'nullable|date',
        ]);

        $exceptionService->reportException($shipment, $validated, $request->user());

        return back()->with('success', 'Operational exception logged and alerted successfully.');
    }

    public function resolveException(Request $request, int $exceptionId, ExceptionService $exceptionService)
    {
        if (! in_array($request->user()->role, ['admin', 'operator'], true)) {
            abort(403, 'Forbidden. Only administrators and logistics operators can update exception status.');
        }

        $exception = ShipmentException::findOrFail($exceptionId);

        $validated = $request->validate([
            'status'           => 'required|in:OPEN,INVESTIGATING,RESOLVED',
            'resolution_notes' => 'nullable|string|max:500',
        ]);

        $exceptionService->updateStatus($exception, $validated['status'], $validated['resolution_notes'], $request->user());

        return back()->with('success', 'Shipment exception status updated successfully.');
    }
}
