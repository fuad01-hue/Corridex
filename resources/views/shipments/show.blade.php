@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Section 1: Consignment Header & Status Summary -->
    <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs flex flex-wrap justify-between items-center gap-4 transition-colors">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Bill of Lading</span>
                <span class="text-xs text-slate-300 dark:text-slate-600">&bull;</span>
                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">{{ $shipment->carrier->name }} ({{ $shipment->carrier->code }})</span>
                <span class="text-xs text-slate-300 dark:text-slate-600">&bull;</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $shipment->carrier->transport_mode }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight font-mono">{{ $shipment->shipment_number }}</h1>
            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $shipment->origin->name }}</span> ({{ $shipment->origin->country }}) &rarr;
                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $shipment->destination->name }}</span> ({{ $shipment->destination->country }})
                &bull; Consignee: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $shipment->customer->name }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Print Manifest Button -->
            <button onclick="window.print()" class="no-print inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 transition-colors" title="Print Cargo Manifest">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Manifest</span>
            </button>

            @php
                $statusClass = match($shipment->current_status) {
                    'DELIVERED' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                    'IN_TRANSIT' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
                    'PICKED_UP' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800',
                    'BOOKED' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800',
                    default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                };
            @endphp
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusClass }}">
                Status: {{ $shipment->current_status }}
            </span>

            @if($shipment->is_delayed)
                @php
                    $severityBadge = match($shipment->delay_severity) {
                        'HIGH' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/60 dark:text-red-300 dark:border-red-800',
                        'MEDIUM' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                        'LOW' => 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-950/60 dark:text-yellow-300 dark:border-yellow-800',
                        default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold uppercase border {{ $severityBadge }}">
                    Delayed +{{ $shipment->delay_hours }}h (Severity: {{ $shipment->delay_severity }})
                </span>
                @if($shipment->is_sla_breached)
                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-black uppercase bg-red-600 text-white shadow-xs">
                        SLA BREACH
                    </span>
                @endif
            @else
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                    On Schedule (0h Delay)
                </span>
            @endif
        </div>
    </div>

    <!-- Section 2: Interactive Route Map & Telemetry Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Leaflet.js Dynamic Theme Map -->
        <div class="lg:col-span-2 bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col transition-colors">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Intercontinental Route &amp; GPS Waypoints</h2>
                <span id="map-provider-badge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                    @if(config('services.geoapify.key'))
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span id="map-provider-text">Geoapify Maritime Vector</span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span id="map-provider-text">Esri Maritime Canvas</span>
                    @endif
                </span>
            </div>
            <div id="shipment-map" class="w-full h-96 rounded-lg border border-slate-200 dark:border-slate-800 overflow-hidden z-0"></div>
        </div>

        <!-- Telemetry & Live Hub Weather Panel -->
        <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs space-y-5 transition-colors">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Route & ETA Telemetry</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Deterministic scheduled vs estimated arrival analytics</p>
            </div>

            <dl class="text-xs space-y-2.5 border-t border-slate-100 dark:border-slate-800 pt-3">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800/60">
                    <dt class="text-slate-500 dark:text-slate-400">Primary Cargo:</dt>
                    <dd class="font-bold text-slate-800 dark:text-slate-200">{{ $shipment->cargo_type }}</dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800/60">
                    <dt class="text-slate-500 dark:text-slate-400">Current Hub:</dt>
                    <dd class="font-bold text-blue-600 dark:text-blue-400">{{ $shipment->currentLocation->name ?? $shipment->origin->name }}</dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800/60">
                    <dt class="text-slate-500 dark:text-slate-400">Expected Departure:</dt>
                    <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $shipment->expected_departure?->format('d M Y H:i') }}</dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800/60">
                    <dt class="text-slate-500 dark:text-slate-400">Expected Target Arrival:</dt>
                    <dd class="font-medium text-slate-700 dark:text-slate-300">{{ $shipment->expected_arrival?->format('d M Y H:i') }}</dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800/60">
                    <dt class="text-slate-500 dark:text-slate-400">Estimated Arrival (ETA):</dt>
                    <dd class="font-bold {{ $shipment->is_delayed ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $shipment->estimated_arrival?->format('d M Y H:i') }}
                    </dd>
                </div>
                <div class="flex justify-between py-1">
                    <dt class="text-slate-500 dark:text-slate-400">Corridor Rule SLA:</dt>
                    <dd class="font-mono text-slate-700 dark:text-slate-300">
                        @if(str_contains($shipment->shipment_number, 'SEA-EU'))
                            SEA-EU (12h Threshold)
                        @elseif(str_contains($shipment->shipment_number, 'ASIA-NA'))
                            ASIA-NA (18h Threshold)
                        @elseif(str_contains($shipment->shipment_number, 'SEA-AU'))
                            SEA-AU (24h Threshold)
                        @else
                            Global Default (12h Threshold)
                        @endif
                    </dd>
                </div>
            </dl>

            <!-- Weather Telemetry Subsection -->
            <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">Hub Telemetry (Open-Meteo)</h3>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $shipment->currentLocation->code ?? 'HUB' }}</span>
                </div>

                @if($weather['available'])
                    <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-lg p-3 text-xs space-y-1.5 transition-colors">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 dark:text-slate-400">Ambient Temperature:</span>
                            <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $weather['temperature'] }} &deg;C</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 dark:text-slate-400">Surface Wind Velocity:</span>
                            <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $weather['windspeed'] }} km/h</span>
                        </div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic pt-1 border-t border-slate-200 dark:border-slate-700/60 leading-tight">
                            {{ $weather['disclaimer'] ?? 'Point-in-time hub weather telemetry for operational awareness only; not a standalone predictor of shipment delay.' }}
                        </p>
                    </div>
                @else
                    <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-slate-600 dark:text-slate-300 rounded-lg p-3 text-xs">
                        <p class="font-semibold">{{ $weather['message'] ?? 'External weather telemetry temporarily unavailable.' }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic pt-1 mt-1 border-t border-slate-200 dark:border-slate-700/60 leading-tight">
                            Point-in-time hub weather telemetry for operational awareness only; not a standalone predictor of shipment delay.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Section 3: Cargo Manifest Line Items -->
    <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden transition-colors">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex justify-between items-center">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Official Cargo Manifest Line Items</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Itemized cargo specifications, declared weight, and CBM volume</p>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                Gross: {{ number_format($shipment->total_weight_kg) }} kg &bull; {{ $shipment->total_volume_cbm }} CBM
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-3">SKU Identifier</th>
                        <th class="px-6 py-3">Item Description</th>
                        <th class="px-6 py-3 text-center">Quantity</th>
                        <th class="px-6 py-3 text-right">Unit Weight (kg)</th>
                        <th class="px-6 py-3 text-right">Volume (CBM)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($shipment->items as $item)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-3 font-mono font-bold text-slate-900 dark:text-white">{{ $item->sku ?? 'N/A' }}</td>
                        <td class="px-6 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $item->item_name }}</td>
                        <td class="px-6 py-3 text-center font-bold">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-right font-medium">{{ number_format($item->weight_kg, 2) }}</td>
                        <td class="px-6 py-3 text-right font-medium">{{ number_format($item->volume_cbm, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-slate-400 italic">No specific manifest items recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 4: Operator Action Panels (Admin & Operator Only) -->
    @if(in_array(auth()->user()->role, ['admin', 'operator']))
    <div class="no-print grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form: Append Tracking Milestone -->
        <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs transition-colors">
            <div class="border-b border-slate-200 dark:border-slate-800 pb-3 mb-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Append Tracking Milestone</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Enforces state machine progression and recalculates deterministic delay metrics.</p>
            </div>

            <form method="POST" action="{{ route('shipments.tracking.store', $shipment->id) }}" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Target Lifecycle State</label>
                        <select name="event_status" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                            <option value="{{ $shipment->current_status }}">Maintain Current ({{ $shipment->current_status }})</option>
                            @foreach($allowedNextStatuses as $nextStatus)
                                @if($nextStatus !== $shipment->current_status)
                                    <option value="{{ $nextStatus }}">&rarr; Transition to {{ $nextStatus }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tracking-location-select-search" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Transit Location Hub</label>
                        <x-port-combobox
                            id="tracking-location-select"
                            name="location_id"
                            :initial-location="$selectedTrackingLocation"
                            :initial-options="$locations"
                            :allow-empty="true"
                            :allow-fallback="true"
                            empty-label="Open Waters / Custom Coordinates"
                            placeholder="Search hub, seaport, or UN/LOCODE..."
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 rounded-lg p-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="tracking-latitude" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Waypoint Latitude</label>
                            <button type="button" id="tracking-sync-coords" class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">Sync Port Coords</button>
                        </div>
                        <input id="tracking-latitude" type="number" step="0.0000001" min="-90" max="90" name="latitude" value="{{ old('latitude') }}" placeholder="Auto-filled from hub..." class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                    </div>
                    <div>
                        <label for="tracking-longitude" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Waypoint Longitude (Override Allowed)</label>
                        <input id="tracking-longitude" type="number" step="0.0000001" min="-180" max="180" name="longitude" value="{{ old('longitude') }}" placeholder="Auto-filled from hub..." class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Updated Estimated Arrival (ETA)</label>
                        <input type="datetime-local" name="estimated_arrival" value="{{ $shipment->estimated_arrival?->format('Y-m-d\TH:i') }}" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Reason for Delay (Optional)</label>
                        <input type="text" name="delay_reason" value="{{ $shipment->delay_reason }}" placeholder="e.g. Berth congestion backlog..." class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Milestone Description</label>
                    <input type="text" name="description" required placeholder="Describe checkpoint event..." class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400">
                        <input type="checkbox" name="is_exception" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                        <span>Flag as Exception Milestone</span>
                    </label>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow-xs transition">
                        + Append Tracking Event
                    </button>
                </div>
            </form>
        </div>

        <!-- Form: Report Operational Exception -->
        <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs transition-colors">
            <div class="border-b border-slate-200 dark:border-slate-800 pb-3 mb-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Log Operational Exception</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Trigger alert notifications to consignee and update risk metrics.</p>
            </div>

            <form method="POST" action="{{ route('shipments.exceptions.store', $shipment->id) }}" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Exception Category</label>
                        <select name="exception_type" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                            @foreach(['CUSTOMS_HOLD', 'WEATHER_DELAY', 'PORT_CONGESTION', 'DAMAGED', 'MISROUTED', 'DOCUMENTATION_ISSUE'] as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Severity Rating</label>
                        <select name="severity" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                            <option value="LOW">LOW</option>
                            <option value="MEDIUM" selected>MEDIUM</option>
                            <option value="HIGH">HIGH</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Exception Description</label>
                    <input type="text" name="description" required placeholder="Detailed reason for hold or disruption..." class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Resolution Strategy / Action Notes</label>
                    <input type="text" name="resolution_notes" placeholder="Immediate mitigation steps..." class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg shadow-xs transition">
                        ! File Exception Report
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Section 5: Chronological Timeline & Active Exceptions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chronological Tracking Timeline -->
        <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs transition-colors">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-4">Chronological Tracking History</h2>
            <ol class="relative border-l border-slate-200 dark:border-slate-800 ml-3 space-y-6">
                @forelse($timeline as $event)
                    <li class="ml-6">
                        <span class="absolute -left-2.5 flex h-5 w-5 items-center justify-center rounded-full {{ $event->is_exception ? 'bg-red-500 ring-red-100 dark:ring-red-950/60' : 'bg-blue-500 ring-blue-100 dark:ring-blue-950/60' }} ring-4">
                            <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                        </span>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-900 dark:text-white">
                                {{ $event->event_status }}
                                @if($event->location)
                                    &bull; <span class="text-blue-600 dark:text-blue-400">{{ $event->location->name }}</span>
                                @endif
                            </span>
                            <time class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $event->occurred_at?->format('d M Y H:i') }}</time>
                        </div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                            Coordinates: ({{ number_format($event->latitude, 4) }}, {{ number_format($event->longitude, 4) }}) &bull; Recorded by: {{ $event->recorder->name ?? 'System' }}
                        </p>
                        <p class="text-xs text-slate-700 dark:text-slate-300 mt-2 bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-lg border border-slate-100 dark:border-slate-700/60">
                            {{ $event->description }}
                        </p>
                    </li>
                @empty
                    <li class="text-xs text-slate-400 italic">No tracking milestones recorded yet.</li>
                @endforelse
            </ol>
        </div>

        <!-- Operational Exceptions & Mitigation Log -->
        <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs transition-colors">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-4">Operational Exceptions Log</h2>
            <div class="space-y-4">
                @forelse($shipment->exceptions as $exc)
                    <div class="border border-slate-200 dark:border-slate-700/60 rounded-xl p-4 bg-slate-50/60 dark:bg-slate-800/40 space-y-2 transition-colors">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $exc->status === 'RESOLVED' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/60 dark:text-red-300 dark:border-red-800' }} border">
                                    {{ $exc->exception_type }} ({{ $exc->status }})
                                </span>
                                <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 ml-1">Severity: {{ $exc->severity }}</span>
                            </div>
                            <time class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $exc->detected_at?->format('d M Y H:i') }}</time>
                        </div>

                        <p class="text-xs text-slate-800 dark:text-slate-200 font-medium">{{ $exc->description }}</p>

                        @if($exc->resolution_notes)
                            <div class="text-[11px] text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800/80 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700/60">
                                <strong>Resolution Notes:</strong> {{ $exc->resolution_notes }}
                                @if($exc->resolved_at)
                                    <span class="block text-[10px] text-slate-400 dark:text-slate-500 mt-1">
                                        Resolved on: {{ $exc->resolved_at->format('d M Y H:i') }} (Duration: {{ $exc->resolution_hours ?? '-' }}h)
                                    </span>
                                @endif
                            </div>
                        @endif

                        <!-- Update/Resolve Controls for Operators -->
                        @if(in_array(auth()->user()->role, ['admin', 'operator']) && $exc->status !== 'RESOLVED')
                        <form method="POST" action="{{ route('exceptions.resolve', $exc->id) }}" class="no-print mt-3 pt-3 border-t border-slate-200 dark:border-slate-700/60 flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                <option value="INVESTIGATING" {{ $exc->status === 'INVESTIGATING' ? 'selected' : '' }}>INVESTIGATING</option>
                                <option value="RESOLVED">RESOLVED</option>
                            </select>
                            <input type="text" name="resolution_notes" placeholder="Resolution action notes..." class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs flex-1 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-blue-500">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1 rounded-lg transition">
                                Update
                            </button>
                        </form>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">No operational exceptions filed for this shipment.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
/* Leaflet High-End Custom DivIcon & Popup Styling */
.custom-leaflet-marker {
    background: transparent !important;
    border: none !important;
}
.leaflet-popup-content-wrapper {
    background: #0f172a !important; /* dark slate-900 */
    color: #f8fafc !important;
    border-radius: 0.75rem !important;
    padding: 0 !important;
    border: 1px solid #334155 !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.6), 0 8px 10px -6px rgba(0, 0, 0, 0.5) !important;
}
.leaflet-popup-content {
    margin: 0 !important;
    line-height: 1.4 !important;
}
.leaflet-popup-tip {
    background: #0f172a !important;
    border: 1px solid #334155 !important;
}
.leaflet-container {
    background-color: #f8fafc !important;
}
.dark .leaflet-container {
    background-color: #0b1120 !important;
}
.dark .leaflet-tile-pane {
    filter: brightness(1.45) contrast(1.15) saturate(1.2);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const originLat = {{ (float) $shipment->origin->latitude }};
    const originLng = {{ (float) $shipment->origin->longitude }};
    
    // Strict world bounds to prevent gray margins
    const worldBounds = [[-85, -180], [85, 180]];

    // Initialize map with restricted container bounds to prevent gray margin artifact
    const map = L.map('shipment-map', {
        minZoom: 2,
        maxBounds: worldBounds,
        maxBoundsViscosity: 1.0,
        worldCopyJump: false
    }).setView([originLat, originLng], 3);

    const mapConfig = {
        geoapifyKey: @json(config('services.geoapify.key', '')),
        provider: @json(config('cargotrace.map.provider', 'esri')),
        mapboxKey: @json(config('cargotrace.map.mapbox_key', '')),
        maptilerKey: @json(config('cargotrace.map.maptiler_key', '')),
        cartoKey: @json(config('cargotrace.map.carto_key', ''))
    };

    let currentTileLayer = null;
    let outerGlowPolyline = null;
    let innerPathPolyline = null;

    function updateShipmentMapBadge(isGeoapify) {
        const badge = document.getElementById('map-provider-badge');
        if (!badge) return;

        if (isGeoapify) {
            badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span><span id="map-provider-text">Geoapify Maritime Vector</span>';
        } else {
            badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span id="map-provider-text">Esri Maritime Canvas</span>';
        }
    }

    // 1. Dual-Theme High-End Tiles (Geoapify Dual Theme with Esri Fallback)
    function applyMapTiles() {
        const isDark = document.documentElement.classList.contains('dark');
        let tileUrl;
        let tileOptions;

        const geoapifyKey = (mapConfig.geoapifyKey || '').trim();

        if (geoapifyKey.length > 0) {
            tileUrl = isDark
                ? `https://maps.geoapify.com/v1/tile/dark-matter/{z}/{x}/{y}.png?apiKey=${geoapifyKey}`
                : `https://maps.geoapify.com/v1/tile/positron/{z}/{x}/{y}.png?apiKey=${geoapifyKey}`;
            tileOptions = {
                maxZoom: 19,
                noWrap: true,
                maxBounds: worldBounds,
                bounds: worldBounds,
                attribution: 'Powered by <a href="https://www.geoapify.com/" target="_blank">Geoapify</a> | &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
            };
            updateShipmentMapBadge(true);
        } else if (mapConfig.mapboxKey && mapConfig.mapboxKey.trim().length > 0) {
            const style = isDark ? 'dark-v11' : 'navigation-day-v1';
            tileUrl = `https://api.mapbox.com/styles/v1/mapbox/${style}/tiles/{z}/{x}/{y}?access_token=${encodeURIComponent(mapConfig.mapboxKey.trim())}`;
            tileOptions = {
                maxZoom: 19,
                tileSize: 512,
                zoomOffset: -1,
                noWrap: true,
                maxBounds: worldBounds,
                bounds: worldBounds,
                attribution: '&copy; <a href="https://www.mapbox.com/" target="_blank">Mapbox</a> &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
            };
        } else {
            // Smart Keyless Fallback: Esri ArcGIS Canvas (World_Dark_Gray_Base / World_Light_Gray_Base)
            tileUrl = isDark
                ? 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}'
                : 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}';
            tileOptions = {
                maxZoom: 16,
                noWrap: true,
                maxBounds: worldBounds,
                bounds: worldBounds,
                attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, OpenStreetMap'
            };
            updateShipmentMapBadge(false);
        }

        if (currentTileLayer) {
            map.removeLayer(currentTileLayer);
        }

        currentTileLayer = L.tileLayer(tileUrl, tileOptions).addTo(map);

        // Fallback safety for tile errors (e.g. invalid key or network issue)
        currentTileLayer.on('tileerror', function () {
            if (mapConfig.geoapifyKey) {
                console.warn('Geoapify tile rejected. Falling back to Esri ArcGIS Canvas.');
                mapConfig.geoapifyKey = '';
                applyMapTiles();
            }
        });
    }

    applyMapTiles();

    // 2. Custom Glowing SVG DivIcons
    function createOriginIcon() {
        return L.divIcon({
            className: 'custom-leaflet-marker',
            iconSize: [36, 36],
            iconAnchor: [18, 18],
            popupAnchor: [0, -18],
            html: `
                <div class="relative flex items-center justify-center w-9 h-9 group">
                    <span class="absolute inset-0 rounded-full bg-emerald-500/30 blur-xs"></span>
                    <div class="w-8 h-8 rounded-full bg-emerald-600 border-2 border-white dark:border-slate-900 shadow-xl flex items-center justify-center text-white ring-2 ring-emerald-500/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 3v18m0-18a4 4 0 00-4 4v2m4-6a4 4 0 014 4v2M5 12h14M7 21h10" />
                        </svg>
                    </div>
                </div>
            `
        });
    }

    function createCurrentPositionIcon() {
        return L.divIcon({
            className: 'custom-leaflet-marker',
            iconSize: [42, 42],
            iconAnchor: [21, 21],
            popupAnchor: [0, -21],
            html: `
                <div class="relative flex items-center justify-center w-10 h-10">
                    <span class="animate-ping absolute inline-flex h-9 w-9 rounded-full bg-cyan-400 opacity-75"></span>
                    <span class="absolute inline-flex h-6 w-6 rounded-full bg-blue-500/40"></span>
                    <div class="relative w-5 h-5 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-400 border-2 border-white dark:border-slate-950 shadow-lg flex items-center justify-center text-white">
                        <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                    </div>
                </div>
            `
        });
    }

    function createDestinationIcon() {
        return L.divIcon({
            className: 'custom-leaflet-marker',
            iconSize: [36, 36],
            iconAnchor: [18, 18],
            popupAnchor: [0, -18],
            html: `
                <div class="relative flex items-center justify-center w-9 h-9">
                    <span class="absolute inset-0 rounded-full bg-indigo-500/30 blur-xs"></span>
                    <div class="w-8 h-8 rounded-full bg-indigo-600 border-2 border-white dark:border-slate-900 shadow-xl flex items-center justify-center text-white ring-2 ring-indigo-500/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                    </div>
                </div>
            `
        });
    }

    function createExceptionIcon() {
        return L.divIcon({
            className: 'custom-leaflet-marker',
            iconSize: [38, 38],
            iconAnchor: [19, 19],
            popupAnchor: [0, -19],
            html: `
                <div class="relative flex items-center justify-center w-9 h-9">
                    <span class="animate-ping absolute inline-flex h-8 w-8 rounded-full bg-amber-400 opacity-80"></span>
                    <div class="relative w-8 h-8 rounded-full bg-amber-500 border-2 border-white dark:border-slate-950 shadow-xl flex items-center justify-center text-slate-950 ring-2 ring-amber-400/50">
                        <svg class="w-4 h-4 text-slate-950 fill-current" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            `
        });
    }

    function createWaypointIcon() {
        return L.divIcon({
            className: 'custom-leaflet-marker',
            iconSize: [26, 26],
            iconAnchor: [13, 13],
            popupAnchor: [0, -13],
            html: `
                <div class="flex items-center justify-center w-6 h-6">
                    <div class="w-3.5 h-3.5 rounded-full bg-sky-500 border-2 border-white dark:border-slate-900 shadow-md"></div>
                </div>
            `
        });
    }

    // 3. Sleek Dark-Slate Styled Leaflet Popups
    const liveWeather = @json($weather);

    function buildPopup(pt) {
        let weatherBadge = '';
        if (liveWeather && liveWeather.available && liveWeather.temperature !== null) {
            weatherBadge = `
                <div class="mt-2.5 pt-2 border-t border-slate-800 flex items-center justify-between text-[10px]">
                    <span class="text-slate-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        Hub Telemetry:
                    </span>
                    <span class="font-mono font-bold text-sky-400">${liveWeather.temperature}&deg;C &bull; ${liveWeather.windspeed ?? 0} km/h</span>
                </div>
            `;
        }

        return `
            <div class="font-sans text-xs bg-slate-900 text-slate-100 p-3.5 rounded-xl border border-slate-700/80 shadow-2xl min-w-[220px]">
                <div class="flex items-center justify-between gap-2 pb-2 border-b border-slate-800">
                    <span class="font-bold text-white text-xs tracking-tight">${pt.label}</span>
                    <span class="font-mono text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-950 text-blue-300 border border-blue-800">${pt.code || 'HUB'}</span>
                </div>
                <div class="space-y-1.5 mt-2.5 text-[11px]">
                    <div class="flex justify-between text-slate-400">
                        <span>Country:</span>
                        <span class="font-medium text-slate-200">${pt.country || 'International'}</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Status:</span>
                        <span class="font-bold ${pt.is_exception ? 'text-amber-400' : 'text-emerald-400'}">${pt.status}</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Timestamp:</span>
                        <span class="font-mono text-[10px] text-slate-300">${pt.time || '-'}</span>
                    </div>
                </div>
                ${weatherBadge}
            </div>
        `;
    }

    const points = @json($mapPoints);
    const latLngs = [];

    points.forEach(function (pt) {
        latLngs.push([pt.lat, pt.lng]);
        
        let markerIcon;
        if (pt.is_exception) {
            markerIcon = createExceptionIcon();
        } else if (pt.type === 'ORIGIN') {
            markerIcon = createOriginIcon();
        } else if (pt.type === 'DESTINATION') {
            markerIcon = createDestinationIcon();
        } else if (pt.is_current) {
            markerIcon = createCurrentPositionIcon();
        } else {
            markerIcon = createWaypointIcon();
        }

        L.marker([pt.lat, pt.lng], { icon: markerIcon })
            .addTo(map)
            .bindPopup(buildPopup(pt), { maxWidth: 280, closeButton: false });
    });

    // 4. Maritime Route Polyline Styling (Glowing Lane + Inner Dashed Path)
    if (latLngs.length > 1) {
        outerGlowPolyline = L.polyline(latLngs, {
            color: '#38bdf8',
            weight: 5,
            opacity: 0.3,
            lineCap: 'round',
            lineJoin: 'round'
        }).addTo(map);

        innerPathPolyline = L.polyline(latLngs, {
            color: '#0284c7',
            weight: 2.5,
            opacity: 0.9,
            dashArray: '6, 8',
            lineCap: 'round',
            lineJoin: 'round'
        }).addTo(map);

        map.fitBounds(innerPathPolyline.getBounds(), { padding: [40, 40], maxZoom: 6 });
    }

    // Invalidate size to eliminate gray margins on initial load & resize
    setTimeout(function () {
        map.invalidateSize();
    }, 200);

    window.addEventListener('resize', function () {
        map.invalidateSize();
    });

    // Dynamic tile update on theme toggle without page reload
    window.addEventListener('theme-changed', applyMapTiles);

    // 5. Auto-fill Tracking Event Coordinates from Selected Transit Location Hub
    const trackingLocationHidden = document.getElementById('tracking-location-select');
    const trackingLatInput = document.getElementById('tracking-latitude');
    const trackingLngInput = document.getElementById('tracking-longitude');
    const trackingSyncBtn = document.getElementById('tracking-sync-coords');
    const trackingForm = trackingLocationHidden ? trackingLocationHidden.closest('form') : null;

    let currentTrackingCoords = trackingLocationHidden ? {
        lat: trackingLocationHidden.dataset.lat || '',
        lng: trackingLocationHidden.dataset.lng || '',
    } : { lat: '', lng: '' };

    function syncTrackingCoords(force) {
        if (!trackingLatInput || !trackingLngInput) return;
        if (force || (!trackingLatInput.value && !trackingLngInput.value)) {
            trackingLatInput.value = currentTrackingCoords.lat || '';
            trackingLngInput.value = currentTrackingCoords.lng || '';
        }
    }

    if (trackingForm) {
        trackingForm.addEventListener('port-selected', function (event) {
            const { fieldName, port } = event.detail || {};
            if (fieldName === 'location_id') {
                currentTrackingCoords = port
                    ? { lat: String(port.latitude), lng: String(port.longitude) }
                    : { lat: '', lng: '' };
                syncTrackingCoords(true);
            }
        });
    }

    syncTrackingCoords(false);

    if (trackingSyncBtn) {
        trackingSyncBtn.addEventListener('click', function () {
            syncTrackingCoords(true);
        });
    }
});
</script>
@endsection
