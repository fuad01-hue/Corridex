<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExceptionResource;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExceptionApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $shipmentId): JsonResponse
    {
        $shipment = Shipment::findOrFail($shipmentId);

        if ($request->user()->role === 'customer' && $shipment->customer_id !== $request->user()->id) {
            return $this->errorResponse(
                'Forbidden. You do not have permission to view exceptions for this consignment.',
                [],
                'FORBIDDEN',
                403
            );
        }

        $exceptions = $shipment->exceptions()->with('reporter')->latest('detected_at')->get();

        return $this->successResponse(
            ExceptionResource::collection($exceptions),
            'Shipment exceptions retrieved successfully.'
        );
    }

    public function store(Request $request, int $shipmentId, ExceptionService $exceptionService): JsonResponse
    {
        $shipment = Shipment::findOrFail($shipmentId);

        $validated = $request->validate([
            'exception_type'   => 'required|in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE',
            'severity'         => 'required|in:LOW,MEDIUM,HIGH',
            'description'      => 'required|string|max:500',
            'resolution_notes' => 'nullable|string|max:500',
            'detected_at'      => 'nullable|date',
            'status'           => 'nullable|in:OPEN,INVESTIGATING,RESOLVED',
        ]);

        $exception = $exceptionService->reportException($shipment, $validated, $request->user());

        return $this->successResponse(
            new ExceptionResource($exception->load('reporter')),
            'Shipment exception reported successfully.',
            201
        );
    }

    public function update(Request $request, int $id, ExceptionService $exceptionService): JsonResponse
    {
        $exception = ShipmentException::findOrFail($id);

        $validated = $request->validate([
            'status'           => 'required|in:OPEN,INVESTIGATING,RESOLVED',
            'resolution_notes' => 'nullable|string|max:500',
        ]);

        $updated = $exceptionService->updateStatus(
            $exception,
            $validated['status'],
            $validated['resolution_notes'] ?? null,
            $request->user()
        );

        return $this->successResponse(
            new ExceptionResource($updated->fresh()->load('reporter')),
            'Shipment exception updated successfully.'
        );
    }
}
