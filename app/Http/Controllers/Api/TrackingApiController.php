<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShipmentResource;
use App\Http\Resources\TrackingEventResource;
use App\Models\Shipment;
use App\Services\TrackingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TrackingApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $shipmentId): JsonResponse
    {
        $shipment = Shipment::findOrFail($shipmentId);

        if ($request->user()->role === 'customer' && $shipment->customer_id !== $request->user()->id) {
            return $this->errorResponse(
                'Forbidden. You do not have permission to view tracking events for this consignment.',
                [],
                'FORBIDDEN',
                403
            );
        }

        $events = $shipment->trackingEvents()->with(['location', 'recorder'])->orderBy('occurred_at', 'asc')->get();

        return $this->successResponse(
            TrackingEventResource::collection($events),
            'Tracking events retrieved successfully.'
        );
    }

    public function store(Request $request, int $shipmentId, TrackingService $trackingService): JsonResponse
    {
        $shipment = Shipment::findOrFail($shipmentId);

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
            $event = $trackingService->appendEvent($shipment, $validated, $request->user());

            return $this->successResponse(
                new TrackingEventResource($event->load(['location', 'recorder'])),
                'Tracking event recorded successfully.',
                201,
                ['shipment' => new ShipmentResource($shipment->fresh()->load(['origin', 'destination', 'carrier']))]
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                ['occurred_at' => [$e->getMessage()]],
                'TIMELINE_INTEGRITY_VIOLATION',
                422
            );
        }
    }
}
