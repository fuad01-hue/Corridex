<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExternalWeatherService
{
    public const OPERATIONAL_DISCLAIMER = 'Point-in-time hub weather telemetry for operational awareness only; not a standalone predictor of shipment delay.';

    /**
     * Retrieve current weather telemetry for the given coordinates.
     *
     * @param float $latitude
     * @param float $longitude
     * @return array{available: bool, temperature: float|null, windspeed: float|null, weathercode: int|null, disclaimer?: string, message?: string}
     */
    public function getCurrentWeather(float $latitude, float $longitude): array
    {
        try {
            $timeout = config('cargotrace.weather_api.timeout_seconds', 5);
            $baseUrl = config('cargotrace.weather_api.base_url', 'https://api.open-meteo.com/v1/forecast');

            $response = Http::timeout($timeout)
                ->retry(1, 200)
                ->get($baseUrl, [
                    'latitude'        => $latitude,
                    'longitude'       => $longitude,
                    'current_weather' => true,
                ]);

            if ($response->failed() || empty($response->json('current_weather'))) {
                return $this->fallbackResponse('External weather telemetry temporarily unavailable.');
            }

            $weather = $response->json('current_weather');

            return [
                'available'   => true,
                'temperature' => isset($weather['temperature']) ? (float) $weather['temperature'] : null,
                'windspeed'   => isset($weather['windspeed']) ? (float) $weather['windspeed'] : null,
                'weathercode' => isset($weather['weathercode']) ? (int) $weather['weathercode'] : null,
                'disclaimer'  => self::OPERATIONAL_DISCLAIMER,
            ];
        } catch (Throwable $e) {
            Log::warning('ExternalWeatherService telemetry failure: ' . $e->getMessage());
            return $this->fallbackResponse('External weather telemetry temporarily unavailable.');
        }
    }

    /**
     * Generate structured fallback response when weather telemetry is unreachable.
     */
    private function fallbackResponse(string $message): array
    {
        return [
            'available'   => false,
            'temperature' => null,
            'windspeed'   => null,
            'weathercode' => null,
            'message'     => $message,
            'disclaimer'  => self::OPERATIONAL_DISCLAIMER,
        ];
    }
}
