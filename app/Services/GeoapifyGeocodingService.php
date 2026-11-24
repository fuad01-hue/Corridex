<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeoapifyGeocodingService
{
    public const ENDPOINT = 'https://api.geoapify.com/v1/geocode/search';
    public const TIMEOUT_SECONDS = 5;

    /**
     * Geocode a free-text location query into normalized location objects.
     *
     * @param string $text
     * @param int $limit
     * @return array<int, array{name: string, city: string|null, country: string|null, country_code: string|null, latitude: float, longitude: float, formatted: string}>
     */
    public function geocode(string $text, int $limit = 5): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $apiKey = trim((string) config('services.geoapify.key', ''));
        if ($apiKey === '') {
            Log::info('GeoapifyGeocodingService: Skipped geocoding call because GEOAPIFY_API_KEY is empty.');
            return [];
        }

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->retry(1, 200)
                ->get(self::ENDPOINT, [
                    'text'   => $text,
                    'limit'  => $limit,
                    'format' => 'json',
                    'apiKey' => $apiKey,
                ]);

            if ($response->failed()) {
                Log::warning('GeoapifyGeocodingService upstream error: HTTP ' . $response->status(), [
                    'query'  => $text,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $payload = $response->json();
            $results = $payload['results'] ?? [];

            return $this->normalizeResults($results);
        } catch (Throwable $e) {
            Log::warning('GeoapifyGeocodingService request exception: ' . $e->getMessage(), [
                'query' => $text,
            ]);
            return [];
        }
    }

    /**
     * Normalize raw Geoapify results into clean structured arrays.
     *
     * @param array<int, array<string, mixed>> $rawResults
     * @return array<int, array{name: string, city: string|null, country: string|null, country_code: string|null, latitude: float, longitude: float, formatted: string}>
     */
    public function normalizeResults(array $rawResults): array
    {
        $normalized = [];

        foreach ($rawResults as $item) {
            $lat = $item['lat'] ?? $item['latitude'] ?? null;
            $lon = $item['lon'] ?? $item['longitude'] ?? null;

            if ($lat === null || $lon === null) {
                continue;
            }

            $name = $item['name'] ?? $item['city'] ?? $item['formatted'] ?? 'Unknown Location';
            $city = $item['city'] ?? null;
            $country = $item['country'] ?? null;
            $countryCode = isset($item['country_code']) ? strtoupper((string) $item['country_code']) : null;
            $formatted = $item['formatted'] ?? (string) $name;

            $normalized[] = [
                'name'         => (string) $name,
                'city'         => $city ? (string) $city : null,
                'country'      => $country ? (string) $country : null,
                'country_code' => $countryCode,
                'latitude'     => (float) $lat,
                'longitude'    => (float) $lon,
                'formatted'    => (string) $formatted,
            ];
        }

        return $normalized;
    }
}
