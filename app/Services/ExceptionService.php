<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExceptionService
{
    /**
     * Report an operational exception for a shipment.
     *
     * @param Shipment $shipment
     * @param array $data
     * @param User $actor
     * @return ShipmentException
     */
    public function reportException(Shipment $shipment, array $data, User $actor): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $data, $actor) {
            $exception = ShipmentException::create([
                'shipment_id'      => $shipment->id,
                'reported_by'      => $actor->id,
                'exception_type'   => $data['exception_type'],
                'severity'         => $data['severity'],
                'status'           => $data['status'] ?? 'OPEN',
                'description'      => $data['description'],
                'resolution_notes' => $data['resolution_notes'] ?? null,
                'detected_at'      => isset($data['detected_at']) ? Carbon::parse($data['detected_at']) : now(),
                'resolved_at'      => ($data['status'] ?? 'OPEN') === 'RESOLVED' ? now() : null,
            ]);

            // Dispatch notification alert to the customer
            Notification::create([
                'user_id'     => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title'       => "Exception Alert: {$shipment->shipment_number}",
                'message'     => "Operational exception recorded: {$data['exception_type']} ({$data['severity']}) - {$data['description']}",
                'type'        => 'EXCEPTION_ALERT',
                'is_read'     => false,
            ]);

            // Record Audit Log entry
            AuditLog::create([
                'user_id'     => $actor->id,
                'action'      => 'CREATE_EXCEPTION',
                'entity_type' => 'ShipmentException',
                'entity_id'   => $exception->id,
                'old_values'  => null,
                'new_values'  => $exception->toArray(),
                'ip_address'  => request()?->ip() ?? '127.0.0.1',
                'created_at'  => now(),
            ]);

            return $exception;
        });
    }

    /**
     * Update status and resolution notes of an existing exception.
     *
     * @param ShipmentException $exception
     * @param string $status
     * @param string|null $resolutionNotes
     * @param User $actor
     * @return ShipmentException
     */
    public function updateStatus(ShipmentException $exception, string $status, ?string $resolutionNotes, User $actor): ShipmentException
    {
        return DB::transaction(function () use ($exception, $status, $resolutionNotes, $actor) {
            $oldValues = $exception->only(['status', 'resolved_at', 'resolution_notes']);

            $resolvedAt = ($status === 'RESOLVED')
                ? ($exception->resolved_at ?? now())
                : null;

            $exception->update([
                'status'           => $status,
                'resolution_notes' => $resolutionNotes ?? $exception->resolution_notes,
                'resolved_at'      => $resolvedAt,
            ]);

            $shipment = $exception->shipment;

            // Record Audit Log entry
            AuditLog::create([
                'user_id'     => $actor->id,
                'action'      => 'UPDATE_EXCEPTION_STATUS',
                'entity_type' => 'ShipmentException',
                'entity_id'   => $exception->id,
                'old_values'  => $oldValues,
                'new_values'  => $exception->only(['status', 'resolved_at', 'resolution_notes']),
                'ip_address'  => request()?->ip() ?? '127.0.0.1',
                'created_at'  => now(),
            ]);

            // Dispatch notification alert to the customer
            if ($shipment) {
                Notification::create([
                    'user_id'     => $shipment->customer_id,
                    'shipment_id' => $shipment->id,
                    'title'       => "Exception Status Update: {$shipment->shipment_number}",
                    'message'     => "Exception {$exception->exception_type} status transitioned to {$status}." . ($resolutionNotes ? " Notes: {$resolutionNotes}" : ''),
                    'type'        => 'EXCEPTION_STATUS_UPDATE',
                    'is_read'     => false,
                ]);
            }

            return $exception;
        });
    }
}
