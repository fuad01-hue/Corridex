<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class EtaService
{
    /**
     * Deterministically evaluate shipment delay and SLA breach metrics.
     *
     * @param Shipment $shipment
     * @param CarbonInterface $estimatedArrival
     * @param int|null $customThresholdHours
     * @return array{is_delayed: bool, delay_hours: int, delay_severity: string, is_sla_breached: bool}
     */
    public function evaluateDelay(Shipment $shipment, CarbonInterface $estimatedArrival, ?int $customThresholdHours = null): array
    {
        $expectedArrival = Carbon::parse($shipment->expected_arrival);
        $eta = Carbon::parse($estimatedArrival);

        $delayMinutes = $expectedArrival->diffInMinutes($eta, false);
        $delayHours = $delayMinutes > 0 ? (int) ceil($delayMinutes / 60) : 0;
        $isDelayed = $delayHours > 0;

        $severity = 'NONE';
        if ($isDelayed) {
            $lowLimit = config('cargotrace.delay_severity_thresholds.low', 6);
            $mediumLimit = config('cargotrace.delay_severity_thresholds.medium', 24);

            if ($delayHours <= $lowLimit) {
                $severity = 'LOW';
            } elseif ($delayHours <= $mediumLimit) {
                $severity = 'MEDIUM';
            } else {
                $severity = 'HIGH';
            }
        }

        $threshold = $customThresholdHours ?? $this->resolveThresholdForShipment($shipment);
        $isSlaBreached = $delayHours >= $threshold;

        return [
            'is_delayed'      => $isDelayed,
            'delay_hours'     => $delayHours,
            'delay_severity'  => $severity,
            'is_sla_breached' => $isSlaBreached,
        ];
    }

    /**
     * Resolve corridor-specific SLA breach threshold or fallback to global default.
     */
    public function resolveThresholdForShipment(Shipment $shipment): int
    {
        $number = $shipment->shipment_number ?? '';

        if (str_contains($number, 'SEA-EU')) {
            return config('cargotrace.corridor_thresholds.SEA-EU', 12);
        }

        if (str_contains($number, 'ASIA-NA')) {
            return config('cargotrace.corridor_thresholds.ASIA-NA', 18);
        }

        if (str_contains($number, 'SEA-AU')) {
            return config('cargotrace.corridor_thresholds.SEA-AU', 24);
        }

        // Region-based resolution
        $originRegion = $shipment->origin?->region;
        $destRegion = $shipment->destination?->region;

        if ($originRegion === 'Southeast Asia' && $destRegion === 'Europe') {
            return config('cargotrace.corridor_thresholds.SEA-EU', 12);
        }

        if (str_contains((string) $originRegion, 'Asia') && $destRegion === 'North America') {
            return config('cargotrace.corridor_thresholds.ASIA-NA', 18);
        }

        if ($originRegion === 'Southeast Asia' && $destRegion === 'Australia') {
            return config('cargotrace.corridor_thresholds.SEA-AU', 24);
        }

        return config('cargotrace.sla_breach_threshold_hours', 12);
    }
}
