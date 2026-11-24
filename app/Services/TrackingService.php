<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrackingService
{
    public function __construct(protected EtaService $etaService) {}

    /**
     * Append a new tracking event, validate lifecycle state transition and chronological integrity.
     *
     * @param Shipment $shipment
     * @param array $data
     * @param User $actor
     * @return TrackingEvent
     * @throws InvalidArgumentException
     */
    public function appendEvent(Shipment $shipment, array $data, User $actor): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $data, $actor) {
            $newStatus = $data['event_status'];
            $currentStatus = $shipment->current_status;

            // Enforce valid status transitions
            if ($newStatus !== $currentStatus) {
                $allowed = config("cargotrace.valid_transitions.{$currentStatus}", []);
                if (! in_array($newStatus, $allowed, true)) {
                    throw new InvalidArgumentException(
                        "Invalid status transition from {$currentStatus} to {$newStatus}."
                    );
                }
            }

            // Enforce chronological integrity against latest event
            $latestEvent = $shipment->trackingEvents()->orderByDesc('occurred_at')->first();
            $occurredAt = isset($data['occurred_at']) ? Carbon::parse($data['occurred_at']) : now();

            if ($latestEvent && $occurredAt->lt(Carbon::parse($latestEvent->occurred_at))) {
                throw new InvalidArgumentException(
                    "Occurred at timestamp cannot be earlier than the shipment's latest tracking event."
                );
            }

            $location = isset($data['location_id']) ? Location::find($data['location_id']) : null;
            $lat = $data['latitude'] ?? $location?->latitude ?? $shipment->origin->latitude;
            $lng = $data['longitude'] ?? $location?->longitude ?? $shipment->origin->longitude;

            // Append new TrackingEvent record (strictly append-only)
            $event = TrackingEvent::create([
                'shipment_id'  => $shipment->id,
                'location_id'  => $location?->id,
                'recorded_by'  => $actor->id,
                'event_status' => $newStatus,
                'latitude'     => $lat,
                'longitude'    => $lng,
                'description'  => $data['description'],
                'is_exception' => (bool) ($data['is_exception'] ?? false),
                'occurred_at'  => $occurredAt,
            ]);

            $oldValues = $shipment->only([
                'current_status',
                'current_location_id',
                'estimated_arrival',
                'is_delayed',
                'delay_hours',
                'delay_severity',
                'is_sla_breached',
            ]);

            $updatePayload = [
                'current_status'      => $newStatus,
                'current_location_id' => $location?->id ?? $shipment->current_location_id,
            ];

            // Recompute delay and SLA breach metrics if estimated arrival updated
            if (! empty($data['estimated_arrival'])) {
                $newEta = Carbon::parse($data['estimated_arrival']);
                $delayEval = $this->etaService->evaluateDelay($shipment, $newEta);

                $updatePayload = array_merge($updatePayload, [
                    'estimated_arrival' => $newEta,
                    'is_delayed'        => $delayEval['is_delayed'],
                    'delay_hours'       => $delayEval['delay_hours'],
                    'delay_severity'    => $delayEval['delay_severity'],
                    'is_sla_breached'   => $delayEval['is_sla_breached'],
                    'delay_reason'      => $data['delay_reason'] ?? $shipment->delay_reason,
                ]);
            }

            $shipment->update($updatePayload);

            // Record Audit Log entry
            AuditLog::create([
                'user_id'     => $actor->id,
                'action'      => 'APPEND_TRACKING_EVENT',
                'entity_type' => 'Shipment',
                'entity_id'   => $shipment->id,
                'old_values'  => $oldValues,
                'new_values'  => $updatePayload,
                'ip_address'  => request()?->ip() ?? '127.0.0.1',
                'created_at'  => now(),
            ]);

            // Dispatch In-App Notification for shipment's customer
            Notification::create([
                'user_id'     => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title'       => "Status Update: {$shipment->shipment_number}",
                'message'     => "Shipment status updated to {$newStatus}. {$data['description']}",
                'type'        => $shipment->is_delayed ? 'DELAY_ALERT' : 'STATUS_UPDATE',
                'is_read'     => false,
            ]);

            return $event;
        });
    }
}
