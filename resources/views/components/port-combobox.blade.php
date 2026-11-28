@props([
    'name',
    'id' => null,
    'initialLocation' => null,
    'initialOptions' => [],
    'placeholder' => 'Search port name, UN/LOCODE, city, or country...',
    'emptyLabel' => 'All Ports',
    'allowEmpty' => true,
    'allowFallback' => true,
    'required' => false,
])

@php
    $serializeLoc = function ($loc) {
        if (! $loc) {
            return null;
        }
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
    };

    $initialSelectedData = $serializeLoc($initialLocation);
    $initialOptionsData = collect($initialOptions)->map($serializeLoc)->filter()->values()->all();
@endphp

@once
<script>
window.portCombobox = function (config) {
    return {
        fieldName: config.fieldName,
        searchUrl: config.searchUrl,
        resolveUrl: config.resolveUrl,
        allowEmpty: Boolean(config.allowEmpty),
        allowFallback: Boolean(config.allowFallback),
        required: Boolean(config.required),
        emptyLabel: config.emptyLabel || 'All Ports',
        initialOptions: Array.isArray(config.initialOptions) ? config.initialOptions : [],
        options: Array.isArray(config.initialOptions) ? config.initialOptions : [],
        selectedPort: config.selected || null,
        selectedId: config.selected && config.selected.id ? String(config.selected.id) : '',
        query: '',
        open: false,
        loading: false,
        resolving: false,
        errorMessage: '',
        highlightedIndex: -1,
        debounceTimer: null,
        abortController: null,

        init() {
            if (this.selectedPort) {
                this.query = this.formatLabel(this.selectedPort);
            }
            this.syncValidity();
        },

        formatLabel(port) {
            if (!port) return '';
            const codePart = port.code ? `${port.code} • ` : '';
            const countryPart = port.country || 'International';
            return `${port.name} (${codePart}${countryPart})`;
        },

        syncValidity() {
            if (!this.$refs.searchInput) return;
            if (this.required && !this.selectedId) {
                this.$refs.searchInput.setCustomValidity('Please select a valid port from the search results.');
            } else {
                this.$refs.searchInput.setCustomValidity('');
            }
        },

        openDropdown() {
            this.open = true;
            this.errorMessage = '';
            const trimmed = this.query.trim();
            if (!trimmed || (this.selectedPort && trimmed === this.formatLabel(this.selectedPort))) {
                this.fetchPorts('');
            }
            this.highlightedIndex = this.options.length > 0 ? 0 : -1;
        },

        onInput() {
            this.open = true;
            this.errorMessage = '';
            const term = this.query.trim();

            if (!term) {
                if (this.allowEmpty) {
                    this.selectedPort = null;
                    this.selectedId = '';
                    this.syncValidity();
                    this.emitSelection(null);
                }
                this.options = this.initialOptions;
                this.highlightedIndex = this.options.length > 0 ? 0 : -1;
            } else {
                // Instant local filter over cached initial options while remote query runs
                const lower = term.toLowerCase();
                const instant = this.initialOptions.filter((p) =>
                    (p.name && p.name.toLowerCase().includes(lower)) ||
                    (p.code && p.code.toLowerCase().includes(lower)) ||
                    (p.city && p.city.toLowerCase().includes(lower)) ||
                    (p.country && p.country.toLowerCase().includes(lower))
                );
                if (instant.length > 0) {
                    this.options = instant;
                    this.highlightedIndex = 0;
                } else {
                    this.options = [];
                    this.highlightedIndex = -1;
                }
            }

            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.fetchPorts(term);
            }, 180);
        },

        async fetchPorts(term) {
            if (this.abortController) {
                this.abortController.abort();
            }
            this.abortController = new AbortController();
            this.loading = true;

            try {
                const url = `${this.searchUrl}?q=${encodeURIComponent(term)}&limit=25&fallback=${this.allowFallback ? 1 : 0}`;
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    signal: this.abortController.signal,
                });

                if (!response.ok) {
                    throw new Error(`Search failed (${response.status})`);
                }

                const payload = await response.json();
                this.options = Array.isArray(payload.data) ? payload.data : [];
                this.highlightedIndex = this.options.length > 0 ? 0 : -1;
            } catch (err) {
                if (err.name !== 'AbortError') {
                    this.errorMessage = 'Unable to search ports right now.';
                }
            } finally {
                this.loading = false;
            }
        },

        async selectPort(port) {
            if (!port) {
                this.selectedPort = null;
                this.selectedId = '';
                this.query = '';
                this.open = false;
                this.highlightedIndex = -1;
                this.syncValidity();
                this.emitSelection(null);
                return;
            }

            let resolvedPort = port;

            if (port.source === 'geoapify' && !port.id) {
                this.resolving = true;
                this.errorMessage = '';
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const response = await fetch(this.resolveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({
                            name: port.name,
                            city: port.city,
                            country: port.country,
                            code: port.code,
                            region: port.region,
                            type: port.type || 'PORT',
                            latitude: port.latitude,
                            longitude: port.longitude,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('Failed to save selected location.');
                    }

                    const payload = await response.json();
                    resolvedPort = payload.data;
                } catch (err) {
                    this.errorMessage = 'Could not resolve Geoapify port coordinates.';
                    this.resolving = false;
                    return;
                }
                this.resolving = false;
            }

            this.selectedPort = resolvedPort;
            this.selectedId = String(resolvedPort.id);
            this.query = this.formatLabel(resolvedPort);
            this.open = false;
            this.highlightedIndex = -1;
            this.syncValidity();
            this.emitSelection(resolvedPort);
        },

        clearSelection() {
            this.selectedPort = null;
            this.selectedId = '';
            this.query = '';
            this.options = this.initialOptions;
            this.highlightedIndex = this.options.length > 0 ? 0 : -1;
            this.open = true;
            this.syncValidity();
            this.emitSelection(null);
            this.$nextTick(() => this.$refs.searchInput?.focus());
        },

        handleKeydown(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!this.open) {
                    this.openDropdown();
                    return;
                }
                if (this.options.length > 0) {
                    this.highlightedIndex = (this.highlightedIndex + 1) % this.options.length;
                    this.scrollHighlightedIntoView();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (!this.open) {
                    this.openDropdown();
                    return;
                }
                if (this.options.length > 0) {
                    this.highlightedIndex = this.highlightedIndex <= 0
                        ? this.options.length - 1
                        : this.highlightedIndex - 1;
                    this.scrollHighlightedIntoView();
                }
            } else if (e.key === 'Enter') {
                if (this.open && this.highlightedIndex >= 0 && this.options[this.highlightedIndex]) {
                    e.preventDefault();
                    this.selectPort(this.options[this.highlightedIndex]);
                }
            } else if (e.key === 'Escape') {
                this.open = false;
                if (this.selectedPort) {
                    this.query = this.formatLabel(this.selectedPort);
                }
            }
        },

        scrollHighlightedIntoView() {
            this.$nextTick(() => {
                const list = this.$refs.listbox;
                if (!list) return;
                const item = list.querySelector(`[data-index="${this.highlightedIndex}"]`);
                if (item) {
                    item.scrollIntoView({ block: 'nearest' });
                }
            });
        },

        closeOutside() {
            if (!this.open) return;
            this.open = false;
            if (this.selectedPort) {
                this.query = this.formatLabel(this.selectedPort);
            } else {
                this.query = '';
            }
        },

        emitSelection(port) {
            this.$el.dispatchEvent(new CustomEvent('port-selected', {
                bubbles: true,
                detail: {
                    fieldName: this.fieldName,
                    port: port,
                },
            }));
        },
    };
};
</script>
@endonce

<div
    x-data="portCombobox({
        fieldName: @js($name),
        searchUrl: @js(route('locations.search')),
        resolveUrl: @js(route('locations.resolve')),
        allowEmpty: @js($allowEmpty),
        allowFallback: @js($allowFallback),
        required: @js($required),
        emptyLabel: @js($emptyLabel),
        selected: @js($initialSelectedData),
        initialOptions: @js($initialOptionsData),
    })"
    @click.outside="closeOutside()"
    class="relative"
>
    <input
        type="hidden"
        name="{{ $name }}"
        @if($id) id="{{ $id }}" @endif
        :value="selectedId"
        :data-lat="selectedPort?.latitude ?? ''"
        :data-lng="selectedPort?.longitude ?? ''"
        :data-code="selectedPort?.code ?? ''"
    >

    <div class="relative flex items-center">
        <input
            x-ref="searchInput"
            type="text"
            @if($id) id="{{ $id }}-search" @endif
            x-model="query"
            @focus="openDropdown()"
            @input="onInput()"
            @keydown="handleKeydown($event)"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            role="combobox"
            :aria-expanded="open.toString()"
            aria-autocomplete="list"
            class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg pl-3 pr-14 py-2 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors"
        >

        <div class="absolute right-2 flex items-center gap-1">
            <template x-if="loading || resolving">
                <svg class="w-3.5 h-3.5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </template>

            <button
                type="button"
                x-show="query.length > 0 || selectedId !== ''"
                @click="clearSelection()"
                title="Clear selection"
                class="p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <button
                type="button"
                @click="open ? (open = false) : openDropdown()"
                title="Toggle port list"
                class="p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded transition-colors"
            >
                <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>
    </div>

    <p x-show="errorMessage" x-text="errorMessage" class="mt-1 text-[10px] text-red-500"></p>

    <div
        x-show="open"
        x-transition.opacity.duration.100ms
        x-ref="listbox"
        role="listbox"
        class="absolute z-50 mt-1 w-full max-h-64 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-xl divide-y divide-slate-100 dark:divide-slate-800 text-xs"
        style="display: none;"
    >
        <template x-if="allowEmpty">
            <button
                type="button"
                @click="selectPort(null)"
                class="w-full text-left px-3 py-2 flex items-center justify-between text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/70 transition-colors"
            >
                <span class="italic" x-text="emptyLabel"></span>
                <span class="text-[10px] font-mono uppercase text-slate-400">Reset</span>
            </button>
        </template>

        <template x-for="(port, idx) in options" :key="(port.id ?? 'geo') + '-' + port.name + '-' + idx">
            <button
                type="button"
                :data-index="idx"
                @click="selectPort(port)"
                @mouseenter="highlightedIndex = idx"
                :class="highlightedIndex === idx ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-900 dark:text-blue-100' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60'"
                class="w-full text-left px-3 py-2 flex items-start justify-between gap-2 transition-colors"
            >
                <div class="min-w-0">
                    <div class="font-semibold truncate" x-text="port.name"></div>
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate">
                        <span x-text="port.city || '-'"></span>,
                        <span x-text="port.country || 'International'"></span>
                        <span class="font-mono text-slate-400 dark:text-slate-500" x-text="`• ${Number(port.latitude).toFixed(3)}, ${Number(port.longitude).toFixed(3)}`"></span>
                    </div>
                </div>
                <div class="shrink-0 flex flex-col items-end gap-0.5">
                    <span
                        x-show="port.code"
                        x-text="port.code"
                        class="px-1.5 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700"
                    ></span>
                    <span
                        x-show="port.source === 'geoapify'"
                        class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800"
                    >Geoapify Live</span>
                </div>
            </button>
        </template>

        <div x-show="!loading && options.length === 0" class="px-3 py-3 text-center text-slate-400 dark:text-slate-500 italic">
            No matching seaports found. Try another port name, city, or UN/LOCODE.
        </div>
    </div>
</div>
