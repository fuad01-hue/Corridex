<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\GeoapifyGeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Fast JSON search across local seaports & transit hubs with optional
     * Geoapify Geocode Autocomplete fallback when local matches < 5.
     */
    public function search(Request $request, GeoapifyGeocodingService $geocodingService): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = max(1, min(25, (int) $request->query('limit', 25)));
        $allowFallback = $request->boolean('fallback', true);

        $query = Location::query();

        if ($q !== '') {
            $like = "%{$q}%";
            $prefix = "{$q}%";

            $query->where(function ($sub) use ($like) {
                $sub->where('name', 'LIKE', $like)
                    ->orWhere('code', 'LIKE', $like)
                    ->orWhere('city', 'LIKE', $like)
                    ->orWhere('country', 'LIKE', $like);
            })->orderByRaw(
                'CASE WHEN UPPER(COALESCE(code, \'\')) = UPPER(?) THEN 0 WHEN UPPER(COALESCE(code, \'\')) LIKE UPPER(?) THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END',
                [$q, $prefix, $prefix]
            );
        }

        $localLocations = $query->orderBy('name')->limit($limit)->get();

        $results = $localLocations->map(fn (Location $loc) => $this->serializeLocation($loc))->values()->all();
        $localCount = count($results);
        $geoapifyCount = 0;

        if ($allowFallback && $q !== '' && mb_strlen($q) >= 2 && $localCount < 5 && trim((string) config('services.geoapify.key', '')) !== '') {
            $remaining = max(1, $limit - $localCount);
            $geoResults = $geocodingService->autocomplete($q, min(5, $remaining));

            foreach ($geoResults as $item) {
                if ($this->isDuplicateResult($results, $item)) {
                    continue;
                }

                $results[] = [
                    'id'        => null,
                    'code'      => $item['country_code'] ?? null,
                    'name'      => $item['name'],
                    'city'      => $item['city'] ?? $item['name'],
                    'country'   => $item['country'] ?? 'International',
                    'region'    => null,
                    'type'      => 'PORT',
                    'latitude'  => (float) $item['latitude'],
                    'longitude' => (float) $item['longitude'],
                    'formatted' => $item['formatted'],
                    'source'    => 'geoapify',
                ];
                $geoapifyCount++;
            }
        }

        return response()->json([
            'data' => $results,
            'meta' => [
                'query'          => $q,
                'local_count'    => $localCount,
                'geoapify_count' => $geoapifyCount,
            ],
        ]);
    }

    /**
     * Resolve or create a local Location record from a live Geoapify selection
     * so bookings and tracking milestones can reference a persisted location_id.
     */
    public function resolve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'city'      => 'nullable|string|max:100',
            'country'   => 'nullable|string|max:100',
            'code'      => 'nullable|string|max:20',
            'region'    => 'nullable|string|max:100',
            'type'      => 'nullable|string|max:30',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $name = trim($validated['name']);
        $country = trim((string) ($validated['country'] ?? '')) ?: 'International';
        $city = trim((string) ($validated['city'] ?? '')) ?: $name;
        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        $existing = Location::query()
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->whereRaw('LOWER(country) = LOWER(?)', [$country])
            ->first();

        if (! $existing) {
            $existing = Location::query()
                ->whereBetween('latitude', [$lat - 0.005, $lat + 0.005])
                ->whereBetween('longitude', [$lng - 0.005, $lng + 0.005])
                ->whereRaw('LOWER(name) = LOWER(?)', [$name])
                ->first();
        }

        if ($existing) {
            return response()->json([
                'data' => $this->serializeLocation($existing),
            ]);
        }

        $code = ! empty($validated['code']) ? strtoupper(trim((string) $validated['code'])) : null;

        $location = Location::create([
            'code'      => $code,
            'name'      => $name,
            'city'      => $city,
            'country'   => $country,
            'region'    => ! empty($validated['region']) ? trim((string) $validated['region']) : null,
            'type'      => strtoupper(trim((string) ($validated['type'] ?? 'PORT'))),
            'latitude'  => $lat,
            'longitude' => $lng,
        ]);

        return response()->json([
            'data' => $this->serializeLocation($location),
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeLocation(Location $loc): array
    {
        return [
            'id'        => $loc->id,
            'code'      => $loc->code,
            'name'      => $loc->name,
            'city'      => $loc->city,
            'country'   => $loc->country,
            'region'    => $loc->region,
            'type'      => $loc->type,
            'latitude'  => (float) $loc->latitude,
            'longitude' => (float) $loc->longitude,
            'source'    => 'local',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $existingResults
     * @param array<string, mixed> $candidate
     */
    protected function isDuplicateResult(array $existingResults, array $candidate): bool
    {
        $candName = mb_strtolower(trim((string) ($candidate['name'] ?? '')));
        $candCountry = mb_strtolower(trim((string) ($candidate['country'] ?? '')));

        foreach ($existingResults as $existing) {
            $exName = mb_strtolower(trim((string) ($existing['name'] ?? '')));
            $exCountry = mb_strtolower(trim((string) ($existing['country'] ?? '')));

            if ($candName === $exName && $candCountry === $exCountry) {
                return true;
            }
        }

        return false;
    }
}
