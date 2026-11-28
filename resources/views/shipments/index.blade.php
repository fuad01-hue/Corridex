@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Header Title & Action -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Global Shipment Directory</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Search, filter, and track intercontinental cargo manifests, carriers, and routing status.</p>
        </div>
        @if(in_array(auth()->user()->role, ['admin', 'operator']))
        <a href="#new-shipment-form" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-xs transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span>Book New Shipment</span>
        </a>
        @endif
    </div>

    <!-- Multi-Param Server-Side Filter Toolbar -->
    <form method="GET" action="{{ route('shipments.index') }}" class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-xs space-y-4 transition-colors">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Search Identifier</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="TRC-2026 / Cargo..." class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Lifecycle Status</label>
                <select name="status" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                    <option value="">All Statuses</option>
                    @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Carrier</label>
                <select name="carrier_id" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                    <option value="">All Carriers</option>
                    @foreach($carriers as $c)
                        <option value="{{ $c->id }}" {{ (string) request('carrier_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->transport_mode }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Origin Port</label>
                <x-port-combobox
                    name="origin_location_id"
                    :initial-location="$selectedOriginFilter"
                    :initial-options="$locations"
                    :allow-empty="true"
                    :allow-fallback="false"
                    empty-label="All Origin Ports"
                    placeholder="All Origin Ports (type to search...)"
                />
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Destination Port</label>
                <x-port-combobox
                    name="destination_location_id"
                    :initial-location="$selectedDestFilter"
                    :initial-options="$locations"
                    :allow-empty="true"
                    :allow-fallback="false"
                    empty-label="All Destination Ports"
                    placeholder="All Destination Ports (type to search...)"
                />
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Delay Condition</label>
                <select name="delay_status" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                    <option value="">All Conditions</option>
                    <option value="delayed" {{ request('delay_status') === 'delayed' ? 'selected' : '' }}>Delayed Only</option>
                    <option value="ontime" {{ request('delay_status') === 'ontime' ? 'selected' : '' }}>On-Time Only</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('shipments.index') }}" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-lg transition-colors">
                Reset Filters
            </a>
            <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                Apply Filters
            </button>
        </div>
    </form>

    <!-- Filtered Results Table -->
    <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-3.5">Shipment #</th>
                        <th class="px-6 py-3.5">Cargo Manifest & Consignee</th>
                        <th class="px-6 py-3.5">Carrier</th>
                        <th class="px-6 py-3.5">Route Corridor</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">ETA / Delay Metric</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($shipments as $shipment)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-white text-xs">
                            {{ $shipment->shipment_number }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $shipment->cargo_type }}</div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500">{{ $shipment->customer->name ?? '-' }} &bull; {{ number_format($shipment->total_weight_kg) }} kg ({{ $shipment->total_volume_cbm }} CBM)</div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                            {{ $shipment->carrier->name ?? '-' }}
                            <span class="block text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $shipment->carrier->code }} &bull; {{ $shipment->carrier->transport_mode }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $shipment->origin->city ?? $shipment->origin->name }} &rarr; {{ $shipment->destination->city ?? $shipment->destination->name }}</div>
                            <div class="text-[10px] text-slate-400 dark:text-slate-500">{{ $shipment->origin->country }} to {{ $shipment->destination->country }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusClass = match($shipment->current_status) {
                                    'DELIVERED' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                                    'IN_TRANSIT' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
                                    'PICKED_UP' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800',
                                    'BOOKED' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusClass }}">
                                {{ $shipment->current_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($shipment->is_delayed)
                                @php
                                    $severityBadge = match($shipment->delay_severity) {
                                        'HIGH' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/60 dark:text-red-300 dark:border-red-800',
                                        'MEDIUM' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                                        'LOW' => 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-950/60 dark:text-yellow-300 dark:border-yellow-800',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                    };
                                @endphp
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $severityBadge }}">
                                        Delayed +{{ $shipment->delay_hours }}h ({{ $shipment->delay_severity }})
                                    </span>
                                    @if($shipment->is_sla_breached)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-red-600 text-white">
                                            SLA BREACH
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800">
                                    On Schedule
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('shipments.show', $shipment->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/60 px-3 py-1.5 rounded-lg border border-blue-200 dark:border-blue-800 transition">
                                <span>Track & Map</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400 italic">No consignments match the selected query criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40">
            {{ $shipments->links() }}
        </div>
    </div>

    <!-- Section 5: Authorized Shipment Booking Panel (Admin & Operator Only) -->
    @if(in_array(auth()->user()->role, ['admin', 'operator']))
    <div id="new-shipment-form" class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs scroll-mt-20 transition-colors">
        <div class="border-b border-slate-200 dark:border-slate-800 pb-4 mb-5">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Book New International Shipment</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Initiate a multimodal consignment manifest, record initial CREATED tracking milestone, and trigger customer alerts.</p>
        </div>

        <form method="POST" action="{{ route('shipments.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            @csrf
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Customer / Consignee</label>
                <select name="customer_id" required class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id }}">{{ $cust->name }} ({{ $cust->email }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Logistics Carrier</label>
                <select name="carrier_id" required class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                    @foreach($carriers as $car)
                        <option value="{{ $car->id }}">{{ $car->name }} ({{ $car->transport_mode }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Cargo Type / Description</label>
                <input type="text" name="cargo_type" required placeholder="e.g. Industrial Automation Equipment" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
            </div>

            <div>
                <label for="booking-origin-location-search" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Origin Port / Terminal</label>
                <x-port-combobox
                    id="booking-origin-location"
                    name="origin_location_id"
                    :initial-location="$selectedBookingOrigin"
                    :initial-options="$locations"
                    :allow-empty="false"
                    :allow-fallback="true"
                    :required="true"
                    placeholder="Search origin port, UN/LOCODE, or city..."
                />
            </div>

            <div>
                <label for="booking-destination-location-search" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Destination Port / Terminal</label>
                <x-port-combobox
                    id="booking-destination-location"
                    name="destination_location_id"
                    :initial-location="$selectedBookingDest"
                    :initial-options="$locations"
                    :allow-empty="false"
                    :allow-fallback="true"
                    :required="true"
                    placeholder="Search destination port, UN/LOCODE, or city..."
                />
                <p id="booking-dest-coords-preview" class="mt-1 text-[10px] font-mono text-slate-400 dark:text-slate-500"></p>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Weight (kg)</label>
                    <input type="number" step="0.01" name="total_weight_kg" value="{{ old('total_weight_kg', 8500) }}" required class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Volume (CBM)</label>
                    <input type="number" step="0.01" name="total_volume_cbm" value="{{ old('total_volume_cbm', 32) }}" required class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                </div>
            </div>

            <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 rounded-lg p-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="booking-origin-lat" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Initial Berth Latitude (Auto-filled)</label>
                        <button type="button" id="booking-reset-coords" class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">Reset to Port</button>
                    </div>
                    <input id="booking-origin-lat" type="number" step="0.0000001" min="-90" max="90" name="latitude" value="{{ old('latitude') }}" placeholder="e.g. -6.0938000" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                </div>
                <div>
                    <label for="booking-origin-lng" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Initial Berth Longitude (Manual Override Allowed)</label>
                    <input id="booking-origin-lng" type="number" step="0.0000001" min="-180" max="180" name="longitude" value="{{ old('longitude') }}" placeholder="e.g. 106.8878000" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Expected Departure</label>
                <input type="datetime-local" name="expected_departure" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Expected Target Arrival</label>
                <input type="datetime-local" name="expected_arrival" value="{{ now()->addDays(10)->format('Y-m-d\TH:i') }}" required class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-lg shadow-xs transition text-xs">
                    + Confirm & Dispatch Booking
                </button>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const bookingForm = document.querySelector('#new-shipment-form form');
        const originHidden = document.getElementById('booking-origin-location');
        const destHidden = document.getElementById('booking-destination-location');
        const latInput = document.getElementById('booking-origin-lat');
        const lngInput = document.getElementById('booking-origin-lng');
        const destPreview = document.getElementById('booking-dest-coords-preview');
        const resetBtn = document.getElementById('booking-reset-coords');

        let currentOriginCoords = originHidden ? {
            lat: originHidden.dataset.lat || '',
            lng: originHidden.dataset.lng || '',
        } : { lat: '', lng: '' };

        function syncOriginCoords(force) {
            if (!latInput || !lngInput) return;
            if (force || (!latInput.value && !lngInput.value)) {
                latInput.value = currentOriginCoords.lat || '';
                lngInput.value = currentOriginCoords.lng || '';
            }
        }

        function syncDestPreview(lat, lng, code) {
            if (!destPreview) return;
            if (!lat || !lng) {
                destPreview.textContent = '';
                return;
            }
            destPreview.textContent = `Target ${code || 'PORT'}: ${Number(lat).toFixed(4)}, ${Number(lng).toFixed(4)}`;
        }

        if (bookingForm) {
            bookingForm.addEventListener('port-selected', function (event) {
                const { fieldName, port } = event.detail || {};
                if (fieldName === 'origin_location_id') {
                    currentOriginCoords = port
                        ? { lat: String(port.latitude), lng: String(port.longitude) }
                        : { lat: '', lng: '' };
                    syncOriginCoords(true);
                } else if (fieldName === 'destination_location_id') {
                    if (port) {
                        syncDestPreview(port.latitude, port.longitude, port.code);
                    } else {
                        syncDestPreview('', '', '');
                    }
                }
            });
        }

        syncOriginCoords(false);
        if (destHidden) {
            syncDestPreview(destHidden.dataset.lat, destHidden.dataset.lng, destHidden.dataset.code);
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                syncOriginCoords(true);
            });
        }
    });
    </script>
    @endif
</div>
@endsection
