<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Corridex — Multi-Corridor Freight Visibility Engine for precision route analytics across SEA-EU, ASIA-NA, and SEA-AU maritime lanes.">
    <title>Corridex — Multi-Corridor Freight Visibility Engine</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-icon.png') }}">

    <!-- Pre-load blocking script to prevent Flash of Unstyled Content (FOUC) -->
    <script>
        if (localStorage.theme === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>

    <!-- Tailwind CSS CDN with class dark mode strategy -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#151d2a',
                            950: '#0b0f17',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js for lightweight, reactive interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 dark:bg-[#080C14] dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-200 selection:bg-blue-600 selection:text-white" x-data="corridexLanding()">

    <!-- Top Navigation Header -->
    <header class="bg-white/95 dark:bg-[#0B1120]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800/80 sticky top-0 z-50 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Brand Logo & Global Badge -->
                <div class="flex items-center space-x-8">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-blue-600 text-white shadow-md shadow-blue-600/25 ring-1 ring-white/10 group-hover:scale-105 transition p-1.5">
                            <img src="{{ asset('images/logo-white.png') }}" alt="CX Logo" class="w-full h-auto object-contain">
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                <span class="font-extrabold text-base tracking-tight text-slate-900 dark:text-white leading-none">CORRIDEX</span>
                                <span class="text-[9px] font-mono font-bold uppercase tracking-widest px-1.5 py-0.2 rounded bg-blue-50 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">ENGINE</span>
                            </div>
                            <span class="text-[9px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Multi-Corridor Freight Visibility Engine</span>
                        </div>
                    </a>

                    <!-- Navigation Links -->
                    <nav class="hidden md:flex space-x-1 text-xs font-semibold">
                        <a href="#tracking-section" class="px-3 py-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 transition">
                            Track Consignment
                        </a>
                        <a href="#corridors-section" class="px-3 py-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 transition">
                            Trade Corridors SLA
                        </a>
                    </nav>
                </div>

                <!-- Right Controls: Theme Toggle & Sign In -->
                <div class="flex items-center space-x-3">
                    <!-- Dual-Theme Switcher Button -->
                    <button type="button" @click="toggleTheme()" aria-label="Toggle theme" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/80 transition focus:outline-none" title="Toggle Light / Dark theme">
                        <!-- Sun Icon (in dark mode) -->
                        <svg class="w-4 h-4 text-amber-400 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                        <!-- Moon Icon (in light mode) -->
                        <svg class="w-4 h-4 text-slate-600 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition">
                            <span>Control Center</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-semibold px-3 py-2 text-slate-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 transition">
                            Sign In
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition">
                            <span>Open Demo</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 space-y-16 py-12">
        <!-- Hero Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                <span>Corridex Engine &bull; Multi-Corridor Freight Visibility</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-black text-slate-900 dark:text-white tracking-tight max-w-4xl mx-auto leading-tight">
                Autonomous Multi-Corridor Freight Visibility &amp; SLA Engine
            </h1>

            <p class="max-w-2xl mx-auto text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                Precision container route tracking across Europe, North America, and Asia-Pacific. Combining deterministic delay calculations, append-only chronological audit integrity, and live waypoint weather telemetry.
            </p>

            <!-- Brand Philosophy Highlight Card -->
            <div class="max-w-3xl mx-auto p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-blue-950/40 via-slate-900/60 to-indigo-950/40 border border-blue-900/40 text-left flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center p-2 shrink-0">
                        <img src="{{ asset('images/logo-white.png') }}" alt="CX Logo" class="w-full h-auto object-contain">
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-400 font-mono">Brand Philosophy</span>
                            <span class="text-slate-500">&bull;</span>
                            <span class="text-xs font-semibold text-slate-200">Corridor + Index</span>
                        </div>
                        <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                            <strong>Corridex</strong> synthesizes <span class="text-blue-300 font-semibold">Corridor</span> (referencing the 3 primary trade lanes: <strong>SEA-EU</strong>, <strong>ASIA-NA</strong>, and <strong>SEA-AU</strong>) and <span class="text-blue-300 font-semibold">Index</span> (a mathematical baseline for real-time schedule reliability and precision route analytics).
                        </p>
                    </div>
                </div>
                <a href="#corridors-section" class="shrink-0 text-xs font-semibold px-3.5 py-2 rounded-lg bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 border border-blue-500/30 transition flex items-center gap-1.5">
                    <span>Explore Corridors &darr;</span>
                </a>
            </div>

            <!-- Quick Live Stats Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-3xl mx-auto pt-2">
                <div class="p-3.5 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-xs text-left">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Trade Corridors</div>
                    <div class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">3 Primary Lanes</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">SEA-EU, ASIA-NA, SEA-AU</div>
                </div>
                <div class="p-3.5 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-xs text-left">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">SLA Accuracy</div>
                    <div class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5">Deterministic</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Exact Hour Calculation</div>
                </div>
                <div class="p-3.5 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-xs text-left">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Waypoint Telemetry</div>
                    <div class="text-xl font-extrabold text-blue-600 dark:text-blue-400 mt-0.5">Append-Only</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Immutable Chronology</div>
                </div>
                <div class="p-3.5 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-xs text-left">
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Live Weather</div>
                    <div class="text-xl font-extrabold text-purple-600 dark:text-purple-400 mt-0.5">Open-Meteo</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Hub Wave &amp; Wind API</div>
                </div>
            </div>
        </section>

        <!-- Interactive Public Consignment Tracking Console -->
        <section id="tracking-section" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-lg transition-colors">
                <div class="text-center max-w-xl mx-auto mb-6">
                    <div class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Public Consignment Search</span>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Track Your International Shipment</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Instant telemetry lookup without login. Select a sample corridor consignment below:</p>
                </div>

                <!-- Sample Consignment Quick Buttons -->
                <div class="flex flex-wrap items-center justify-center gap-2 mb-5">
                    <button type="button" @click="selectSample('TRC-2026-SEA-EU-001')" :class="selectedCode === 'TRC-2026-SEA-EU-001' ? 'ring-2 ring-emerald-500 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-3 py-1.5 rounded-lg text-xs font-mono font-semibold transition border border-slate-200 dark:border-slate-700">
                        TRC-2026-SEA-EU-001 (SEA &rarr; EU)
                    </button>
                    <button type="button" @click="selectSample('TRC-2026-ASIA-NA-002')" :class="selectedCode === 'TRC-2026-ASIA-NA-002' ? 'ring-2 ring-sky-500 bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-3 py-1.5 rounded-lg text-xs font-mono font-semibold transition border border-slate-200 dark:border-slate-700">
                        TRC-2026-ASIA-NA-002 (ASIA &rarr; NA)
                    </button>
                    <button type="button" @click="selectSample('TRC-2026-SEA-AU-003')" :class="selectedCode === 'TRC-2026-SEA-AU-003' ? 'ring-2 ring-indigo-500 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-3 py-1.5 rounded-lg text-xs font-mono font-semibold transition border border-slate-200 dark:border-slate-700">
                        TRC-2026-SEA-AU-003 (SEA &rarr; AU)
                    </button>
                </div>

                <!-- Search Input Bar -->
                <div class="relative max-w-2xl mx-auto mb-6">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" x-model="searchQuery" @keydown.enter="searchShipment()" placeholder="Enter consignment number (e.g. TRC-2026-SEA-EU-001)" class="w-full pl-11 pr-28 py-3.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    <button type="button" @click="searchShipment()" class="absolute inset-y-1.5 right-1.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                        <span>Search</span>
                    </button>
                </div>

                <!-- Live Dynamic Tracking Result Box -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-5 bg-slate-50/70 dark:bg-[#0B1120] space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" :class="activeResult.isDelayed ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                            <span class="font-mono font-bold text-sm text-slate-900 dark:text-white" x-text="activeResult.number"></span>
                            <span class="text-xs px-2 py-0.5 rounded font-bold uppercase" :class="activeResult.statusBadgeClass" x-text="activeResult.status"></span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-slate-500">Carrier:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="activeResult.carrier"></span>
                            <span class="text-slate-400">&bull;</span>
                            <span class="text-slate-500">Corridor SLA:</span>
                            <span class="font-mono font-bold" :class="activeResult.isSlaBreached ? 'text-red-500' : 'text-emerald-500'" x-text="activeResult.slaInfo"></span>
                        </div>
                    </div>

                    <!-- Origin to Destination Route Visualizer -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 py-2">
                        <div>
                            <div class="text-[10px] font-bold uppercase text-slate-400">Origin Port</div>
                            <div class="text-sm font-bold text-slate-800 dark:text-slate-100" x-text="activeResult.origin"></div>
                            <div class="text-xs text-slate-500" x-text="activeResult.originTerminal"></div>
                        </div>
                        <div class="text-center flex flex-col items-center justify-center">
                            <div class="text-[10px] font-mono text-slate-400" x-text="activeResult.transitMode"></div>
                            <div class="w-full flex items-center gap-1 my-1">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <div class="h-0.5 flex-1 bg-gradient-to-r from-blue-500 via-sky-400 to-emerald-500"></div>
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            </div>
                            <div class="text-[11px] font-semibold text-slate-600 dark:text-slate-300" x-text="activeResult.currentLocation"></div>
                        </div>
                        <div class="sm:text-right">
                            <div class="text-[10px] font-bold uppercase text-slate-400">Destination Port</div>
                            <div class="text-sm font-bold text-slate-800 dark:text-slate-100" x-text="activeResult.destination"></div>
                            <div class="text-xs text-slate-500" x-text="activeResult.destTerminal"></div>
                        </div>
                    </div>

                    <!-- Metrics & Live Weather Strip -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-200 dark:border-slate-800 text-xs">
                        <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-400 block text-[10px]">Estimated vs Baseline</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="activeResult.etaText"></span>
                            <span class="block text-[11px] mt-0.5 font-semibold" :class="activeResult.isDelayed ? 'text-amber-500' : 'text-emerald-500'" x-text="activeResult.delayText"></span>
                        </div>
                        <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-400 block text-[10px]">Cargo Manifest</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="activeResult.cargo"></span>
                            <span class="block text-[11px] mt-0.5 text-slate-500" x-text="activeResult.weightVolume"></span>
                        </div>
                        <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-400 block text-[10px]">Destination Hub Weather (Open-Meteo)</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="activeResult.weather"></span>
                            <span class="block text-[11px] mt-0.5 text-blue-500" x-text="activeResult.seaConditions"></span>
                        </div>
                    </div>

                    <!-- Exception Notification Banner if delayed -->
                    <template x-if="activeResult.exceptionNotice">
                        <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span x-text="activeResult.exceptionNotice"></span>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <!-- Section: Strategic Trade Corridors Matrix -->
        <section id="corridors-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Multi-Corridor Architecture</div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Contracted Trade Corridors &amp; SLA Limits</h2>
                </div>
                <div class="px-3 py-1 rounded-lg text-xs font-mono bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                    SLA Engine: is_sla_breached = delay_hours &gt; corridor_threshold
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Corridor 1 -->
                <div class="bg-white dark:bg-[#111827] border border-emerald-200 dark:border-emerald-800/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between transition relative overflow-hidden">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Corridor 1 &bull; SEA &rarr; EU
                            </span>
                            <span class="font-mono text-xs font-extrabold text-emerald-600 dark:text-emerald-400">12h SLA Limit</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Southeast Asia to Northern Europe</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">SGSIN / IDJKT &rarr; NLRTM / DEHAM via Suez Transit</p>
                        </div>
                        <div class="space-y-2 text-xs border-t border-slate-100 dark:border-slate-800/80 pt-3">
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span class="text-slate-400">Freight Mode:</span>
                                <span class="font-semibold">Deep-Sea Container</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span class="text-slate-400">Cargo Type:</span>
                                <span class="font-semibold">Industrial Electronics</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span class="text-slate-400">SLA Breach Trigger:</span>
                                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">&gt; 12 Hours Delay</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 mt-4 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Corridor Metric:</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">On-Time Delivery Rate (%)</span>
                    </div>
                </div>

                <!-- Corridor 2 -->
                <div class="bg-white dark:bg-[#111827] border border-sky-200 dark:border-sky-800/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between transition relative overflow-hidden">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                Corridor 2 &bull; ASIA &rarr; NA
                            </span>
                            <span class="font-mono text-xs font-extrabold text-sky-600 dark:text-sky-400">18h SLA Limit</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">East Asia to North America</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">CNSHA / KRPUS &rarr; USLAX / USLGB Transpacific Route</p>
                        </div>
                        <div class="space-y-2 text-xs border-t border-slate-100 dark:border-slate-800/80 pt-3">
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span class="text-slate-400">Freight Mode:</span>
                                <span class="font-semibold">Transpacific Ocean Liner</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span class="text-slate-400">Cargo Type:</span>
                                <span class="font-semibold">Automotive &amp; Consumer</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span class="text-slate-400">SLA Breach Trigger:</span>
                                <span class="font-mono font-bold text-sky-600 dark:text-sky-400">&gt; 18 Hours Delay</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 mt-4 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Corridor Metric:</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">Average Delay Hours</span>
                    </div>
                </div>

                <!-- Corridor 3 -->
                <div class="bg-white dark:bg-[#111827] border border-indigo-200 dark:border-indigo-800/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between transition relative overflow-hidden">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                Corridor 3 &bull; SEA &rarr; AU
                            </span>
                            <span class="font-mono text-xs font-extrabold text-indigo-600 dark:text-indigo-400">24h SLA Limit</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Southeast Asia to Australia</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">SGSIN / IDJKT &rarr; AUSYD / AUMEL Tasman Sea Corridor</p>
                        </div>
                        <div class="space-y-2 text-xs border-t border-slate-100 dark:border-slate-800/80 pt-3">
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span class="text-slate-400">Freight Mode:</span>
                                <span class="font-semibold">Cold Chain &amp; Reefer Cargo</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span class="text-slate-400">Cargo Type:</span>
                                <span class="font-semibold">Perishables &amp; Minerals</span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span class="text-slate-400">SLA Breach Trigger:</span>
                                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">&gt; 24 Hours Delay</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 mt-4 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Corridor Metric:</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">MTTR Resolution Time</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Academic Evaluation / Demo Credentials Console -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            Academic &amp; Recruiter Evaluation
                        </span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1">Pre-Seeded Multi-Role Accounts</h3>
                    </div>
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Open Login Screen &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <div class="font-sans font-bold text-purple-600 dark:text-purple-400 text-sm mb-1">Global Administrator</div>
                        <div class="text-slate-700 dark:text-slate-200">admin@cargotrace.test</div>
                        <div class="text-slate-400 mt-0.5">Password: password</div>
                        <div class="text-[10px] font-sans text-slate-500 mt-2">Full platform visibility, carrier control, and immutable audit logs.</div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <div class="font-sans font-bold text-blue-600 dark:text-blue-400 text-sm mb-1">Logistics Operator</div>
                        <div class="text-slate-700 dark:text-slate-200">operator@cargotrace.test</div>
                        <div class="text-slate-400 mt-0.5">Password: password</div>
                        <div class="text-[10px] font-sans text-slate-500 mt-2">Milestone logging, route tracking, and exception management.</div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <div class="font-sans font-bold text-emerald-600 dark:text-emerald-400 text-sm mb-1">Customer / Consignee</div>
                        <div class="text-slate-700 dark:text-slate-200">customer@cargotrace.test</div>
                        <div class="text-slate-400 mt-0.5">Password: password</div>
                        <div class="text-[10px] font-sans text-slate-500 mt-2">Strict tenant isolation: view own shipments and manifest items.</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Global Enterprise Footer -->
    <footer class="border-t border-slate-200 dark:border-slate-800/80 py-8 bg-white dark:bg-[#080C14] transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Corridex Logo" class="w-6 h-6 rounded-md">
                <span class="font-extrabold text-slate-800 dark:text-slate-200">CORRIDEX</span>
                <span>&bull;</span>
                <span>&copy; {{ date('Y') }} Corridex &bull; Multi-Corridor Freight Visibility Engine. All rights reserved.</span>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <a href="#tracking-section" class="hover:text-blue-500 transition">Track Consignment</a>
                <a href="#corridors-section" class="hover:text-blue-500 transition">Trade Corridors SLA</a>
                <a href="{{ route('login') }}" class="hover:text-blue-500 transition">Operator Portal</a>
            </div>
        </div>
    </footer>

    <!-- Alpine State and Logic -->
    <script>
    function corridexLanding() {
        const sampleData = {
            'TRC-2026-SEA-EU-001': {
                number: 'TRC-2026-SEA-EU-001',
                status: 'IN_TRANSIT',
                statusBadgeClass: 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
                carrier: 'MSC Mediterranean Shipping Co',
                origin: 'Jakarta (IDJKT)',
                originTerminal: 'Tanjung Priok Container Terminal',
                transitMode: 'Deep-Sea Maritime &bull; Suez Transit',
                currentLocation: 'Waypoint: Suez Canal Transit Hub',
                destination: 'Rotterdam (NLRTM)',
                destTerminal: 'Maasvlakte Deepwater Hub',
                slaInfo: '12h SLA Limit (Breached: 48h delay)',
                isSlaBreached: true,
                isDelayed: true,
                etaText: 'Live ETA: +48h beyond contract baseline',
                delayText: 'Delay: 48 Hours &bull; Severity: HIGH',
                cargo: 'Industrial Electronics & Power Inverters',
                weightVolume: '18,500 kg &bull; 68.5 CBM',
                weather: 'Rotterdam: 14°C, Wind 18 km/h NW',
                seaConditions: 'Moderate Swell &bull; Low Berthing Risk',
                exceptionNotice: 'Customs Hold: Supplementary manifest audit underway at Suez Canal Transit Hub.'
            },
            'TRC-2026-ASIA-NA-002': {
                number: 'TRC-2026-ASIA-NA-002',
                status: 'IN_TRANSIT',
                statusBadgeClass: 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
                carrier: 'Maersk Line Ocean Transport',
                origin: 'Shanghai (CNSHA)',
                originTerminal: 'Yangshan Deepwater Port',
                transitMode: 'Transpacific High-Volume Link',
                currentLocation: 'Waypoint: Mid-Pacific Navigation Track',
                destination: 'Los Angeles (USLAX)',
                destTerminal: 'Pier 400 Container Terminal',
                slaInfo: '18h SLA Limit (Compliant: 14h delay)',
                isSlaBreached: false,
                isDelayed: true,
                etaText: 'Live ETA: +14h (Within 18h SLA buffer)',
                delayText: 'Delay: 14 Hours &bull; Severity: MEDIUM',
                cargo: 'Automotive Subassemblies & EV Batteries',
                weightVolume: '24,200 kg &bull; 82.0 CBM',
                weather: 'Los Angeles: 21°C, Wind 11 km/h SW',
                seaConditions: 'Calm Sea &bull; Optimal Docking Conditions',
                exceptionNotice: 'Weather Reroute: Navigating south of Pacific low-pressure swell. SLA remains intact.'
            },
            'TRC-2026-SEA-AU-003': {
                number: 'TRC-2026-SEA-AU-003',
                status: 'DELIVERED',
                statusBadgeClass: 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                carrier: 'MSC Mediterranean Shipping Co',
                origin: 'Singapore (SGSIN)',
                originTerminal: 'Tuas Mega Port Terminal 2',
                transitMode: 'Tasman Sea Cold-Chain Route',
                currentLocation: 'Final Milestone: Destination Terminal Port Botany',
                destination: 'Sydney (AUSYD)',
                destTerminal: 'Port Botany Container Terminal',
                slaInfo: '24h SLA Limit (On-Time Delivery)',
                isSlaBreached: false,
                isDelayed: false,
                etaText: 'Delivered: 4 hours ahead of baseline schedule',
                delayText: 'Delay: 0 Hours &bull; Severity: NONE',
                cargo: 'Temperature-Controlled Agri-Food',
                weightVolume: '14,800 kg &bull; 54.0 CBM',
                weather: 'Sydney: 22°C, Wind 14 km/h S',
                seaConditions: 'Clear Harbor &bull; Consignment Released',
                exceptionNotice: null
            }
        };

        return {
            selectedCode: 'TRC-2026-SEA-EU-001',
            searchQuery: 'TRC-2026-SEA-EU-001',
            activeResult: sampleData['TRC-2026-SEA-EU-001'],

            selectSample(code) {
                this.selectedCode = code;
                this.searchQuery = code;
                if (sampleData[code]) {
                    this.activeResult = sampleData[code];
                }
            },

            searchShipment() {
                const query = this.searchQuery.trim().toUpperCase();
                if (sampleData[query]) {
                    this.selectedCode = query;
                    this.activeResult = sampleData[query];
                } else {
                    // Default fallback demonstration
                    this.selectedCode = query;
                    this.activeResult = {
                        number: query,
                        status: 'REGISTERED',
                        statusBadgeClass: 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                        carrier: 'Verified Multimodal Carrier',
                        origin: 'Global Departure Hub',
                        originTerminal: 'Container Freight Station',
                        transitMode: 'Multimodal Transit',
                        currentLocation: 'Waypoint: Origin Manifest Pre-Clearance',
                        destination: 'International Discharge Port',
                        destTerminal: 'Customs Examination Terminal',
                        slaInfo: 'Corridor Standard SLA (Active)',
                        isSlaBreached: false,
                        isDelayed: false,
                        etaText: 'Schedule Active &bull; In Transit',
                        delayText: 'Delay: 0 Hours &bull; Severity: NONE',
                        cargo: 'General Containerized Consignment',
                        weightVolume: 'Standard Freight Unit',
                        weather: 'Global Meteorological Data Synced',
                        seaConditions: 'Standard Marine Forecast',
                        exceptionNotice: null
                    };
                }
            },

            toggleTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.theme = 'light';
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.theme = 'dark';
                }
            }
        };
    }
    </script>
</body>
</html>
