<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Services\GeoapifyGeocodingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationApiController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $locations = Location::orderBy('name')->get();

        return $this->successResponse(
            LocationResource::collection($locations),
            'Locations retrieved successfully.'
        );
    }

    /**
     * Search and geocode locations via Geoapify Geocoding API.
     * Accessible to admin and operator roles for finding coordinates of new ports/hubs.
     *
     * @param Request $request
     * @param GeoapifyGeocodingService $geocodingService
     * @return JsonResponse
     */
    public function geocode(Request $request, GeoapifyGeocodingService $geocodingService): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'string', 'nullable'],
            'text'  => ['sometimes', 'string', 'nullable'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        $searchQuery = trim((string) ($validated['text'] ?? $validated['query'] ?? ''));
        if ($searchQuery === '') {
            return $this->errorResponse(
                'A text or query parameter is required for geocoding.',
                [],
                'VALIDATION_ERROR',
                422
            );
        }

        $limit = isset($validated['limit']) ? (int) $validated['limit'] : 5;
        $results = $geocodingService->geocode($searchQuery, $limit);

        return $this->successResponse(
            $results,
            'Geocoding results retrieved successfully.',
            200,
            [
                'count' => count($results),
                'query' => $searchQuery,
            ]
        );
    }
}
