<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\TrackingEvent;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Populate global international seaports, maritime transit hubs, and
     * Indonesian/Aceh regional ports using idempotent updateOrCreate.
     * Also supports optional UN/LOCODE CSV import (function containing '1')
     * if database/data/unlocode_seaports.csv is provided.
     */
    public function run(): void
    {
        $seededByCode = [];

        $jsonPath = database_path('data/world_seaports.json');
        if (is_file($jsonPath)) {
            $decoded = json_decode((string) file_get_contents($jsonPath), true);
            if (is_array($decoded)) {
                foreach ($decoded as $port) {
                    if (empty($port['code']) || empty($port['name'])) {
                        continue;
                    }

                    $code = strtoupper(trim((string) $port['code']));
                    $seededByCode[$code] = Location::updateOrCreate(
                        ['code' => $code],
                        [
                            'name'      => trim((string) $port['name']),
                            'city'      => trim((string) ($port['city'] ?? $port['name'])),
                            'country'   => trim((string) ($port['country'] ?? 'International')),
                            'region'    => isset($port['region']) ? trim((string) $port['region']) : null,
                            'type'      => strtoupper(trim((string) ($port['type'] ?? 'PORT'))),
                            'latitude'  => (float) $port['latitude'],
                            'longitude' => (float) $port['longitude'],
                        ]
                    );
                }
            }
        }

        // Optional bundled UN/LOCODE seaports CSV importer (code function '1' = Port)
        // Expected header columns: code,name,city,country,region,function,latitude,longitude
        $csvPath = database_path('data/unlocode_seaports.csv');
        if (is_file($csvPath) && ($handle = fopen($csvPath, 'r')) !== false) {
            $header = fgetcsv($handle);
            if (is_array($header)) {
                $headerMap = array_flip(array_map(fn ($col) => strtolower(trim((string) $col)), $header));

                while (($row = fgetcsv($handle)) !== false) {
                    $fn = isset($headerMap['function']) ? trim((string) ($row[$headerMap['function']] ?? '')) : '1';
                    if (! str_contains($fn, '1')) {
                        continue;
                    }

                    $code = strtoupper(trim((string) ($row[$headerMap['code'] ?? 0] ?? '')));
                    $name = trim((string) ($row[$headerMap['name'] ?? 1] ?? ''));
                    $lat = $row[$headerMap['latitude'] ?? -1] ?? null;
                    $lng = $row[$headerMap['longitude'] ?? -1] ?? null;

                    if ($code === '' || $name === '' || ! is_numeric($lat) || ! is_numeric($lng)) {
                        continue;
                    }

                    // Preserve curated entries already loaded from world_seaports.json
                    if (isset($seededByCode[$code])) {
                        continue;
                    }

                    $seededByCode[$code] = Location::updateOrCreate(
                        ['code' => $code],
                        [
                            'name'      => $name,
                            'city'      => trim((string) ($row[$headerMap['city'] ?? 2] ?? $name)),
                            'country'   => trim((string) ($row[$headerMap['country'] ?? 3] ?? 'International')),
                            'region'    => isset($headerMap['region']) ? trim((string) ($row[$headerMap['region']] ?? '')) ?: null : null,
                            'type'      => 'PORT',
                            'latitude'  => (float) $lat,
                            'longitude' => (float) $lng,
                        ]
                    );
                }
            }
            fclose($handle);
        }

        // Sync existing tracking events for Suez Canal Transit Hub (and core seeded hubs)
        // so historical vessel markers align with the updated maritime channel coordinates.
        foreach (['EGSUZ', 'IDJKT', 'SGSIN', 'NLRTM', 'USLAX', 'AUSYD'] as $coreCode) {
            if (isset($seededByCode[$coreCode])) {
                $loc = $seededByCode[$coreCode];
                TrackingEvent::where('location_id', $loc->id)->update([
                    'latitude'  => $loc->latitude,
                    'longitude' => $loc->longitude,
                ]);
            }
        }
    }
}
