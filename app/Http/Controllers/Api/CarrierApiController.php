<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarrierResource;
use App\Models\Carrier;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CarrierApiController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();

        return $this->successResponse(
            CarrierResource::collection($carriers),
            'Carriers retrieved successfully.'
        );
    }
}
