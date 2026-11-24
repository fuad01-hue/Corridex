@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" x-data="architectureViewer()">
    <!-- Header Banner -->
    <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs flex flex-wrap justify-between items-center gap-4 transition-colors">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 tracking-wide uppercase">
                    Corridex &bull; Multi-Corridor Freight Visibility Engine
                </span>
                <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">Corridor + Index Specification</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">System Architecture &amp; Database ERD</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-3xl">
                Engineering blueprint for Corridex: derived from <strong>Corridor</strong> (3 strategic trade lanes: SEA-EU, ASIA-NA, SEA-AU) and <strong>Index</strong> (precision route analytics, decoupled 5-tier architecture, deterministic SLA calculations, and an append-only relational schema).
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <button @click="openFullscreenModal()" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <span>Fullscreen Inspector</span>
            </button>
            <a :href="activeTab === 'architecture' ? '{{ asset('images/diagrams/architecture-diagram.svg') }}' : '{{ asset('images/diagrams/erd-diagram.svg') }}'" target="_blank" class="inline-flex items-center gap-1.5 border border-slate-200 dark:border-slate-800 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold px-3.5 py-2.5 rounded-lg shadow-xs transition" title="Open raw SVG in a new tab">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Raw SVG</span>
            </a>
        </div>
    </div>

    <!-- Interactive Diagram Viewer Section -->
    <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden transition-colors">
        <!-- Tab Navigation Bar -->
        <div class="border-b border-slate-200 dark:border-slate-800 px-6 py-4 flex flex-wrap items-center justify-between gap-4 bg-slate-50/60 dark:bg-[#111827]/60">
            <div class="flex items-center space-x-2">
                <button type="button" @click="activeTab = 'architecture'" :class="activeTab === 'architecture' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>5-Layer System Architecture</span>
                </button>
                <button type="button" @click="activeTab = 'erd'" :class="activeTab === 'erd' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zm0 5h16" />
                    </svg>
                    <span>Database ERD (9 Relational Tables)</span>
                </button>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span x-text="activeTab === 'architecture' ? '5 Architectural Layers &bull; 3 Trade Corridors' : '9 Relational Tables &bull; Strict Cardinalities'"></span>
            </div>
        </div>

        <!-- Diagram Canvas Container -->
        <div class="p-4 sm:p-6 bg-[#0b1120] relative flex items-center justify-center min-h-[580px] overflow-x-auto">
            <!-- Architecture Diagram -->
            <div x-show="activeTab === 'architecture'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="w-full flex justify-center">
                <img src="{{ asset('images/diagrams/architecture-diagram.svg') }}" alt="Corridex Multi-Corridor Freight Visibility System Architecture Diagram" class="w-full max-w-[1300px] h-auto rounded-lg shadow-2xl border border-slate-800/80 cursor-zoom-in" @click="openFullscreenModal()" />
            </div>

            <!-- ERD Diagram -->
            <div x-show="activeTab === 'erd'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="w-full flex justify-center">
                <img src="{{ asset('images/diagrams/erd-diagram.svg') }}" alt="Corridex Multi-Corridor Freight Visibility Entity Relationship Diagram" class="w-full max-w-[1300px] h-auto rounded-lg shadow-2xl border border-slate-800/80 cursor-zoom-in" @click="openFullscreenModal()" />
            </div>
        </div>
    </div>

    <!-- Section: 3 Global Trade Corridors SLA Matrix -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Global Trade Corridors &amp; SLA Threshold Matrix</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Deterministic tolerance limits evaluated against live telemetry milestones.</p>
            </div>
            <span class="px-2.5 py-1 rounded-md text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                Formula: delay_hours &gt; corridor_sla &rArr; is_sla_breached
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Corridor 1: SEA -> EU (12h SLA) -->
            <div class="bg-white dark:bg-[#151D2A] border border-emerald-200 dark:border-emerald-800/80 rounded-xl p-5 shadow-xs flex flex-col justify-between transition-colors relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-bl-full pointer-events-none"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded text-[11px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            Corridor 1: SEA &rarr; EU
                        </span>
                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">12h SLA</span>
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Southeast Asia to Europe</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Port of Singapore (SGSIN) &bull; Port Klang &rarr; Rotterdam (NLRTM) &bull; Hamburg</p>

                    <div class="mt-4 space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400">Transit Mode:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Deep-sea Maritime Container</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400">Cargo Focus:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Electronics &amp; Heavy Machinery</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400">Breach Criteria:</span>
                            <span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">&gt; 12 Hours Delay</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-500 dark:text-slate-400">Corridor KPI:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">On-Time Delivery Rate (%)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Severity Escalation:</span>
                    <span class="font-bold text-red-500">Breach = HIGH Severity</span>
                </div>
            </div>

            <!-- Corridor 2: ASIA -> NA (18h SLA) -->
            <div class="bg-white dark:bg-[#151D2A] border border-sky-200 dark:border-sky-800/80 rounded-xl p-5 shadow-xs flex flex-col justify-between transition-colors relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-sky-500/5 rounded-bl-full pointer-events-none"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded text-[11px] font-bold uppercase tracking-wider bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                            Corridor 2: ASIA &rarr; NA
                        </span>
                        <span class="text-xs font-black text-sky-600 dark:text-sky-400 font-mono">18h SLA</span>
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">East Asia to North America</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Port of Shanghai (CNSHA) &bull; Busan &rarr; Los Angeles (USLAX) &bull; Long Beach</p>

                    <div class="mt-4 space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400">Transit Mode:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Transpacific High-Volume Freight</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400">Cargo Focus:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Consumer Goods &amp; Auto Parts</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400">Breach Criteria:</span>
                            <span class="font-mono text-sky-600 dark:text-sky-400 font-bold">&gt; 18 Hours Delay</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-500 dark:text-slate-400">Corridor KPI:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Avg Delay Duration (Hours)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Severity Escalation:</span>
                    <span class="font-bold text-red-500">Breach = HIGH Severity</span>
                </div>
            </div>

            <!-- Corridor 3: SEA -> AU (24h SLA) -->
            <div class="bg-white dark:bg-[#151D2A] border border-indigo-200 dark:border-indigo-800/80 rounded-xl p-5 shadow-xs flex flex-col justify-between transition-colors relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/5 rounded-bl-full pointer-events-none"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded text-[11px] font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            Corridor 3: SEA &rarr; AU
                        </span>
                        <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 font-mono">24h SLA</span>
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Southeast Asia to Australia</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Port of Singapore (SGSIN) &bull; Jakarta &rarr; Sydney (AUSYD) &bull; Melbourne</p>

                    <div class="mt-4 space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400">Transit Mode:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Maritime &amp; Reefer Cold-Chain</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400">Cargo Focus:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Agri-Food &amp; Raw Minerals</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-slate-500 dark:text-slate-400">Breach Criteria:</span>
                            <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">&gt; 24 Hours Delay</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-500 dark:text-slate-400">Corridor KPI:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Avg MTTR Resolution (Hours)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Severity Escalation:</span>
                    <span class="font-bold text-red-500">Breach = HIGH Severity</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: 5 Architectural Tiers Technical Breakdown -->
    <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs transition-colors">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight mb-4">5-Tier Architecture Stack</h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Tier 1 -->
            <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-2">
                <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Tier 1: Client UI</div>
                <div class="font-bold text-sm text-slate-800 dark:text-slate-100">Presentation Layer</div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Blade templates, Tailwind CSS, Leaflet.js interactive waypoint maps, and Chart.js metrics.</p>
            </div>

            <!-- Tier 2 -->
            <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-2">
                <div class="text-[11px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Tier 2: Security</div>
                <div class="font-bold text-sm text-slate-800 dark:text-slate-100">RBAC Middleware</div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Session and Sanctum token guards, RoleMiddleware boundaries (admin, operator, customer), and rate limiters.</p>
            </div>

            <!-- Tier 3 -->
            <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-2">
                <div class="text-[11px] font-bold text-teal-600 dark:text-teal-400 uppercase tracking-wider">Tier 3: Ingress</div>
                <div class="font-bold text-sm text-slate-800 dark:text-slate-100">Thin Controllers</div>
                <p class="text-xs text-slate-500 dark:text-slate-400">HTTP request sanitization, FormRequest authorization gates, and immediate delegation to domain services.</p>
            </div>

            <!-- Tier 4 -->
            <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-2">
                <div class="text-[11px] font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wider">Tier 4: Domain</div>
                <div class="font-bold text-sm text-slate-800 dark:text-slate-100">Service Layer</div>
                <p class="text-xs text-slate-500 dark:text-slate-400">EtaService, ShipmentService, TrackingService, ExceptionService, and ExternalWeatherService.</p>
            </div>

            <!-- Tier 5 -->
            <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-2">
                <div class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Tier 5: Storage</div>
                <div class="font-bold text-sm text-slate-800 dark:text-slate-100">MySQL 8 &amp; Telemetry</div>
                <p class="text-xs text-slate-500 dark:text-slate-400">9 normalized InnoDB tables, foreign key constraints, compound indexes, and Open-Meteo REST integration.</p>
            </div>
        </div>
    </div>

    <!-- Fullscreen Inspector Modal Overlay -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-sm flex items-center justify-center p-4" @keydown.escape.window="modalOpen = false">
        <div class="relative bg-[#0b1120] border border-slate-800 rounded-2xl w-full max-w-7xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden" @click.outside="modalOpen = false">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-900/80">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <span class="text-sm font-bold text-white tracking-wide" x-text="activeTab === 'architecture' ? 'System Architecture &amp; Corridor SLA Engine (High Resolution)' : 'Database ERD (9 Relational Tables High Resolution)'"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="activeTab = activeTab === 'architecture' ? 'erd' : 'architecture'" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700 transition">
                        <span x-text="activeTab === 'architecture' ? 'Switch to ERD Diagram' : 'Switch to Architecture Diagram'"></span>
                    </button>
                    <button type="button" @click="modalOpen = false" class="p-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition" aria-label="Close modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <!-- Modal Content (Scrollable SVG View) -->
            <div class="p-6 overflow-auto flex items-center justify-center bg-[#0b1120]">
                <img :src="activeTab === 'architecture' ? '{{ asset('images/diagrams/architecture-diagram.svg') }}' : '{{ asset('images/diagrams/erd-diagram.svg') }}'" alt="Diagram Fullscreen Inspector" class="w-full h-auto max-w-[1400px] object-contain shadow-2xl rounded-lg" />
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3 border-t border-slate-800 bg-slate-900/80 flex items-center justify-between text-xs text-slate-400">
                <span>Tip: Right-click image and choose "Open Image in New Tab" for native SVG vector zooming.</span>
                <button type="button" @click="modalOpen = false" class="text-xs font-semibold px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function architectureViewer() {
        return {
            activeTab: 'architecture',
            modalOpen: false,
            openFullscreenModal() {
                this.modalOpen = true;
            }
        }
    }
</script>
@endsection
