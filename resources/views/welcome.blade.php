<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Corridex — Modern Multi-Corridor Maritime & Air Freight Visibility Portal.">
    <title>CORRIDEX — Global Container & Freight Visibility Portal</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-icon.png') }}">

    <!-- Pre-load blocking script to prevent Flash of Unstyled Content (FOUC) -->
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();

        window.toggleTheme = function () {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDark } }));
        };
    </script>

    <!-- Tailwind CSS CDN -->
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
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-900 dark:bg-[#070B12] dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-200 selection:bg-sky-500 selection:text-white" x-data="corridexLanding()">

    <!-- Sticky Maritime Navigation Header -->
    <header class="bg-white/95 dark:bg-[#0B111A]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800/80 sticky top-0 z-50 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-8">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-md shadow-sky-500/25 ring-1 ring-white/15 group-hover:scale-105 transition p-1.5">
                            <img src="{{ asset('images/logo-white.png') }}" alt="CX Logo" class="w-full h-auto object-contain">
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                <span class="font-extrabold text-base tracking-tight text-slate-900 dark:text-white leading-none">CORRIDEX</span>
                                <span class="text-[9px] font-mono font-bold uppercase tracking-widest px-1.5 py-0.5 rounded bg-sky-50 dark:bg-sky-950/80 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">PORTAL</span>
                            </div>
                            <span class="text-[9px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Maritime &amp; Air Freight Visibility</span>
                        </div>
                    </a>

                    <nav class="hidden md:flex items-center space-x-1 text-xs font-semibold">
                        <a href="#public-tracking" class="px-3 py-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition">
                            Track Container
                        </a>
                        <a href="#services-section" class="px-3 py-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition">
                            Platform Services
                        </a>
                        <a href="#corridors-section" class="px-3 py-2 rounded-lg text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition">
                            Trade Corridors
                        </a>
                    </nav>
                </div>

                <div class="flex items-center space-x-3">
                    <button type="button" onclick="toggleTheme()" aria-label="Toggle theme" class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/80 transition focus:outline-none" title="Toggle Light / Dark theme">
                        <svg class="w-4 h-4 text-amber-400 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                        <svg class="w-4 h-4 text-slate-600 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl shadow-sm shadow-sky-600/20 transition">
                            <span>Control Center</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-semibold px-3 py-2 text-slate-700 dark:text-slate-200 hover:text-sky-600 dark:hover:text-sky-400 transition">
                            Sign In
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl shadow-sm shadow-sky-600/20 transition">
                            <span>Open Demo</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <!-- ShipsGo-Inspired Ocean Hero & Centered Tracking Search -->
        <section id="public-tracking" class="relative overflow-hidden bg-gradient-to-b from-sky-50 via-white to-slate-50 dark:from-[#0A1628] dark:via-[#08101E] dark:to-[#070B12] border-b border-slate-200/80 dark:border-slate-800/80 pt-12 pb-16">
            <!-- Subtle Background Illustrations (Port Crane & Container Stack SVGs) -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden opacity-25 dark:opacity-15">
                <!-- Left Port Gantry Crane SVG -->
                <svg class="hidden lg:block absolute -left-8 bottom-0 w-72 h-72 text-sky-600 dark:text-sky-400" viewBox="0 0 240 240" fill="none">
                    <path d="M35 220V52H175L205 68H35" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M55 220V52M35 90L55 52M55 90L35 52M35 140H55M35 180H55" stroke="currentColor" stroke-width="3"/>
                    <path d="M135 68V125" stroke="currentColor" stroke-width="2.5" stroke-dasharray="4 4"/>
                    <rect x="112" y="125" width="46" height="24" rx="2" fill="currentColor" fill-opacity="0.25" stroke="currentColor" stroke-width="2.5"/>
                    <rect x="75" y="192" width="54" height="28" rx="2" fill="currentColor" fill-opacity="0.2" stroke="currentColor" stroke-width="2.5"/>
                    <rect x="133" y="192" width="54" height="28" rx="2" fill="currentColor" fill-opacity="0.35" stroke="currentColor" stroke-width="2.5"/>
                    <rect x="104" y="164" width="54" height="28" rx="2" fill="currentColor" fill-opacity="0.25" stroke="currentColor" stroke-width="2.5"/>
                </svg>

                <!-- Right Container Vessel Silhouette SVG -->
                <svg class="hidden lg:block absolute -right-10 bottom-4 w-80 h-64 text-blue-600 dark:text-cyan-400" viewBox="0 0 320 200" fill="none">
                    <path d="M25 145H270L298 112H45L25 145Z" fill="currentColor" fill-opacity="0.18" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
                    <rect x="215" y="72" width="38" height="40" rx="2" fill="currentColor" fill-opacity="0.25" stroke="currentColor" stroke-width="2.5"/>
                    <rect x="223" y="54" width="18" height="18" rx="2" fill="currentColor" fill-opacity="0.35" stroke="currentColor" stroke-width="2"/>
                    <rect x="65" y="88" width="42" height="24" rx="2" fill="currentColor" fill-opacity="0.25" stroke="currentColor" stroke-width="2"/>
                    <rect x="111" y="88" width="42" height="24" rx="2" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="2"/>
                    <rect x="157" y="88" width="42" height="24" rx="2" fill="currentColor" fill-opacity="0.3" stroke="currentColor" stroke-width="2"/>
                    <rect x="88" y="64" width="42" height="24" rx="2" fill="currentColor" fill-opacity="0.2" stroke="currentColor" stroke-width="2"/>
                    <rect x="134" y="64" width="42" height="24" rx="2" fill="currentColor" fill-opacity="0.35" stroke="currentColor" stroke-width="2"/>
                    <path d="M10 162C35 154 60 170 85 162C110 154 135 170 160 162C185 154 210 170 235 162C260 154 285 170 310 162" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>

            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto space-y-4">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-sky-100/90 dark:bg-sky-950/80 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Real-Time Container &amp; Air Cargo Visibility</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                        Track Your Containers &amp; Shipments Worldwide
                    </h1>

                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl mx-auto">
                        Enter a shipment or container reference to inspect live corridor progress, deterministic SLA status, hub weather, and active exceptions.
                    </p>
                </div>

                <!-- Centered Hero Tracking Search Box -->
                <div class="mt-8 max-w-3xl mx-auto bg-white dark:bg-[#0F172A] rounded-2xl shadow-xl shadow-sky-900/5 dark:shadow-black/40 border border-slate-200 dark:border-slate-800 p-4 sm:p-6">
                    <form @submit.prevent="searchShipment()" class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-sky-600 dark:text-sky-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input
                                type="text"
                                x-model="searchQuery"
                                placeholder="Enter tracking number (e.g. TRC-2026-SEA-EU-001)"
                                class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 text-sm font-medium focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:outline-none transition"
                            >
                        </div>
                        <button type="submit" class="px-6 py-3.5 bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-bold rounded-xl shadow-md shadow-sky-600/20 text-sm transition flex items-center justify-center gap-2 shrink-0">
                            <span>Track Shipment</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>

                    <!-- Quick Sample Consignment Chips -->
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">Try sample:</span>
                        <button type="button" @click="selectSample('TRC-2026-SEA-EU-001')" :class="selectedCode === 'TRC-2026-SEA-EU-001' ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-sky-400'" class="px-2.5 py-1 rounded-lg font-mono font-semibold border transition">
                            TRC-2026-SEA-EU-001
                        </button>
                        <button type="button" @click="selectSample('TRC-2026-ASIA-NA-002')" :class="selectedCode === 'TRC-2026-ASIA-NA-002' ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-sky-400'" class="px-2.5 py-1 rounded-lg font-mono font-semibold border transition">
                            TRC-2026-ASIA-NA-002
                        </button>
                        <button type="button" @click="selectSample('TRC-2026-SEA-AU-003')" :class="selectedCode === 'TRC-2026-SEA-AU-003' ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-sky-400'" class="px-2.5 py-1 rounded-lg font-mono font-semibold border transition">
                            TRC-2026-SEA-AU-003
                        </button>
                    </div>

                    <!-- Live Tracking Result Panel -->
                    <div class="mt-5 border border-slate-200 dark:border-slate-800 rounded-xl p-4 sm:p-5 bg-slate-50/90 dark:bg-[#0B1120] space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" :class="activeResult.isDelayed ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                                <span class="font-mono font-bold text-sm text-slate-900 dark:text-white" x-text="activeResult.number"></span>
                                <span class="text-[11px] px-2 py-0.5 rounded-md font-bold uppercase" :class="activeResult.statusBadgeClass" x-text="activeResult.status"></span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="activeResult.carrier"></span>
                                <span class="text-slate-400">&bull;</span>
                                <span class="font-mono font-bold" :class="activeResult.isSlaBreached ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'" x-text="activeResult.slaInfo"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Origin Port</div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white" x-text="activeResult.origin"></div>
                                <div class="text-xs text-slate-500" x-text="activeResult.originTerminal"></div>
                            </div>

                            <div class="text-center flex flex-col items-center justify-center">
                                <div class="text-[10px] font-mono text-slate-500 dark:text-slate-400" x-text="activeResult.transitMode"></div>
                                <div class="w-full flex items-center gap-1.5 my-1.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-sky-500"></div>
                                    <div class="h-0.5 flex-1 bg-gradient-to-r from-sky-500 via-blue-500 to-emerald-500"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                                </div>
                                <div class="text-[11px] font-semibold text-slate-700 dark:text-slate-300" x-text="activeResult.currentLocation"></div>
                            </div>

                            <div class="sm:text-right">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Destination Port</div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white" x-text="activeResult.destination"></div>
                                <div class="text-xs text-slate-500" x-text="activeResult.destTerminal"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-200 dark:border-slate-800 text-xs">
                            <div class="bg-white dark:bg-slate-900/70 rounded-lg p-2.5 border border-slate-200/80 dark:border-slate-800">
                                <div class="text-[10px] font-bold uppercase text-slate-400">ETA &amp; Delay</div>
                                <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-html="activeResult.etaText"></div>
                            </div>
                            <div class="bg-white dark:bg-slate-900/70 rounded-lg p-2.5 border border-slate-200/80 dark:border-slate-800">
                                <div class="text-[10px] font-bold uppercase text-slate-400">Cargo Manifest</div>
                                <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-text="activeResult.cargo"></div>
                            </div>
                            <div class="bg-white dark:bg-slate-900/70 rounded-lg p-2.5 border border-slate-200/80 dark:border-slate-800">
                                <div class="text-[10px] font-bold uppercase text-slate-400">Hub Telemetry</div>
                                <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-text="activeResult.weather"></div>
                            </div>
                        </div>

                        <template x-if="activeResult.exceptionNotice">
                            <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/70 text-xs text-amber-800 dark:text-amber-300 font-medium" x-text="activeResult.exceptionNotice"></div>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4 Colorful Service Highlight Cards -->
        <section id="services-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-bold uppercase tracking-widest text-sky-600 dark:text-sky-400">Unified Freight Intelligence</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1">Everything Needed for Global Cargo Control</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Card 1: Container Tracking -->
                <div class="group rounded-2xl p-6 bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white flex items-center justify-center shadow-md shadow-sky-500/25 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15l4-8h10l4 8M4 15h16v4H4v-4zm5-8V5h6v2" />
                        </svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Container Tracking</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                        Live vessel waypoints, append-only milestone timelines, and interactive Leaflet sea routes from origin berth to discharge terminal.
                    </p>
                </div>

                <!-- Card 2: Air Freight -->
                <div class="group rounded-2xl p-6 bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/25 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Air Freight</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                        High-priority air cargo &amp; multimodal tracking with hub weather telemetry and rapid customs pre-clearance milestones.
                    </p>
                </div>

                <!-- Card 3: Corridor Search -->
                <div class="group rounded-2xl p-6 bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/25 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Corridor Search</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                        Instant UN/LOCODE port lookup across SEA-EU, ASIA-NA, SEA-AU, and Indonesian/Aceh regional seaports with Geoapify fallback.
                    </p>
                </div>

                <!-- Card 4: SLA & Exceptions -->
                <div class="group rounded-2xl p-6 bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-rose-600 text-white flex items-center justify-center shadow-md shadow-rose-500/25 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">SLA &amp; Exceptions</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                        Deterministic delay severity scoring, corridor SLA breach alerts, and structured customs/weather hold resolution workflows.
                    </p>
                </div>
            </div>
        </section>

        <!-- Strategic Trade Corridors -->
        <section id="corridors-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
            <div class="rounded-2xl bg-white dark:bg-[#0F172A] border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">Primary Trade Corridors &amp; SLA Targets</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Contracted delay thresholds automatically enforced by the Corridex ETA engine.</p>
                    </div>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl bg-slate-900 dark:bg-sky-600 hover:bg-slate-800 dark:hover:bg-sky-500 text-white transition shrink-0">
                        <span>Launch Live Radar</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400">SEA-EU</span>
                            <span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold">12h SLA</span>
                        </div>
                        <div class="font-bold text-slate-900 dark:text-white">Southeast Asia &rarr; Northern Europe</div>
                        <div class="text-slate-500 dark:text-slate-400 mt-1">Jakarta / Aceh / Singapore &rarr; Suez &rarr; Rotterdam</div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono font-bold text-sky-700 dark:text-sky-400">ASIA-NA</span>
                            <span class="px-2 py-0.5 rounded bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 font-bold">18h SLA</span>
                        </div>
                        <div class="font-bold text-slate-900 dark:text-white">East Asia &rarr; North America</div>
                        <div class="text-slate-500 dark:text-slate-400 mt-1">Singapore / Shanghai &rarr; Transpacific &rarr; Los Angeles</div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono font-bold text-indigo-700 dark:text-indigo-400">SEA-AU</span>
                            <span class="px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold">24h SLA</span>
                        </div>
                        <div class="font-bold text-slate-900 dark:text-white">Southeast Asia &rarr; Oceania</div>
                        <div class="text-slate-500 dark:text-slate-400 mt-1">Jakarta / Surabaya &rarr; Sydney Port Botany</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-[#0B111A] border-t border-slate-200 dark:border-slate-800 py-5 text-xs text-slate-500 dark:text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Corridex" class="w-5 h-5 rounded">
                <span><strong class="text-slate-800 dark:text-slate-200">CORRIDEX</strong> &bull; Multi-Corridor Freight Visibility Engine</span>
            </div>
            <div class="font-mono text-[11px]">&copy; {{ date('Y') }} Corridex Maritime Telemetry</div>
        </div>
    </footer>

    <script>
    function corridexLanding() {
        const sampleData = {
            'TRC-2026-SEA-EU-001': {
                number: 'TRC-2026-SEA-EU-001',
                status: 'IN_TRANSIT',
                statusBadgeClass: 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
                carrier: 'MSC Mediterranean Shipping Co',
                origin: 'Jakarta (IDJKT)',
                originTerminal: 'Tanjung Priok Container Terminal',
                transitMode: 'Deep-Sea Maritime • Suez Transit',
                currentLocation: 'Waypoint: Suez Canal Transit Hub',
                destination: 'Rotterdam (NLRTM)',
                destTerminal: 'Maasvlakte Deepwater Hub',
                slaInfo: '12h SLA Limit (Breached: 48h delay)',
                isSlaBreached: true,
                isDelayed: true,
                etaText: 'Live ETA: +48h beyond contract baseline',
                cargo: 'Industrial Electronics & Power Inverters',
                weather: 'Rotterdam: 14°C, Wind 18 km/h NW',
                exceptionNotice: 'Customs Hold: Supplementary manifest audit underway at Suez Canal Transit Hub.'
            },
            'TRC-2026-ASIA-NA-002': {
                number: 'TRC-2026-ASIA-NA-002',
                status: 'IN_TRANSIT',
                statusBadgeClass: 'bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800',
                carrier: 'Maersk Line Ocean Transport',
                origin: 'Singapore (SGSIN)',
                originTerminal: 'Tuas Mega Port Terminal',
                transitMode: 'Transpacific High-Volume Link',
                currentLocation: 'Waypoint: Mid-Pacific Navigation Track',
                destination: 'Los Angeles (USLAX)',
                destTerminal: 'Pier 400 Container Terminal',
                slaInfo: '18h SLA Limit (Breached: 20h delay)',
                isSlaBreached: true,
                isDelayed: true,
                etaText: 'Live ETA: +20h beyond baseline',
                cargo: 'Semiconductor Wafers & EV Components',
                weather: 'Los Angeles: 21°C, Wind 11 km/h SW',
                exceptionNotice: 'Weather Delay: Navigating south of Pacific low-pressure swell.'
            },
            'TRC-2026-SEA-AU-003': {
                number: 'TRC-2026-SEA-AU-003',
                status: 'DELIVERED',
                statusBadgeClass: 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                carrier: 'MSC Mediterranean Shipping Co',
                origin: 'Jakarta (IDJKT)',
                originTerminal: 'Tanjung Priok Terminal',
                transitMode: 'Tasman Sea Cold-Chain Route',
                currentLocation: 'Final Milestone: Port Botany Terminal',
                destination: 'Sydney (AUSYD)',
                destTerminal: 'Port Botany Container Terminal',
                slaInfo: '24h SLA Limit (On-Time Delivery)',
                isSlaBreached: false,
                isDelayed: false,
                etaText: 'Delivered: On schedule',
                cargo: 'Medical Diagnostic Scanners',
                weather: 'Sydney: 22°C, Wind 14 km/h S',
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
                if (!query) return;
                if (sampleData[query]) {
                    this.selectedCode = query;
                    this.activeResult = sampleData[query];
                } else {
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
                        etaText: 'Schedule Active • In Transit',
                        cargo: 'General Containerized Consignment',
                        weather: 'Global Meteorological Data Synced',
                        exceptionNotice: null
                    };
                }
            }
        };
    }
    </script>
</body>
</html>
