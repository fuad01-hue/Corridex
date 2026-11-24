@extends('layouts.app')

@section('full_viewport')
<div x-data="marineRadar()" x-init="initRadar()" class="w-screen h-screen overflow-hidden flex bg-[#070B12] text-slate-100 font-sans select-none relative">

    <!-- ========================================================================= -->
    <!-- 1. LEFT DARK NAVIGATION SIDEBAR (MarineTraffic Style)                     -->
    <!-- ========================================================================= -->
    <aside 
        :class="sidebarExpanded ? 'w-64' : 'w-16'" 
        class="h-full bg-[#0B111A] border-r border-slate-800/80 flex flex-col justify-between shrink-0 transition-all duration-300 ease-in-out z-30 shadow-2xl relative"
    >
        <!-- Top Section: Brand & Sidebar Collapse Toggle -->
        <div>
            <!-- Header Bar -->
            <div class="h-16 flex items-center justify-between px-3.5 border-b border-slate-800/80 bg-[#0E1522]">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden group">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-600 text-white shadow-md p-1.5 ring-1 ring-blue-400/40 shrink-0 group-hover:scale-105 transition-transform">
                        <img src="{{ asset('images/logo-white.png') }}" alt="CX" class="w-full h-auto object-contain">
                    </div>
                    <div x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="flex flex-col whitespace-nowrap">
                        <span class="font-black text-sm tracking-tight text-white leading-none">CORRIDEX</span>
                        <span class="text-[9px] font-bold text-cyan-400 uppercase tracking-widest mt-0.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
                            Operational Visibility Dashboard
                        </span>
                    </div>
                </a>

                <!-- Sidebar Collapse Toggle Button -->
                <button 
                    @click="sidebarExpanded = !sidebarExpanded" 
                    type="button" 
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors focus:outline-none"
                    :title="sidebarExpanded ? 'Collapse sidebar' : 'Expand sidebar'"
                >
                    <svg class="w-4 h-4 transition-transform duration-300" :class="sidebarExpanded ? 'rotate-0' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Primary Navigation Links -->
            <nav class="p-2 space-y-1 text-xs font-semibold">
                <!-- Map Radar (Active) -->
                <a 
                    href="{{ route('dashboard') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-blue-600/20 text-cyan-400 border border-blue-500/30 font-bold shadow-xs transition group"
                    title="Live Fleet Radar"
                >
                    <div class="w-5 h-5 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-cyan-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="whitespace-nowrap flex-1">Live Fleet Radar</span>
                    <span x-show="sidebarExpanded" class="px-1.5 py-0.5 text-[9px] font-mono bg-cyan-950 text-cyan-300 rounded border border-cyan-800">LIVE</span>
                </a>

                <!-- Shipments Directory -->
                <a 
                    href="{{ route('shipments.index') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition group"
                    title="Shipments Directory"
                >
                    <div class="w-5 h-5 flex items-center justify-center shrink-0 text-slate-400 group-hover:text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="whitespace-nowrap flex-1">Shipment Directory</span>
                    <span x-show="sidebarExpanded" class="px-1.5 py-0.5 text-[10px] font-mono bg-slate-800 text-slate-300 rounded">{{ $totalShipments }}</span>
                </a>

                <!-- Corridors & SLA HUD Toggle -->
                <button 
                    @click="toggleHud()" 
                    type="button" 
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition group text-left"
                    title="Corridors & SLA Telemetry"
                >
                    <div class="w-5 h-5 flex items-center justify-center shrink-0 text-slate-400 group-hover:text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="whitespace-nowrap flex-1">Corridors & SLA</span>
                    <span x-show="sidebarExpanded" :class="isHudExpanded ? 'rotate-180 text-cyan-400' : 'text-slate-500'" class="transition-transform">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </span>
                </button>

                <!-- Exceptions & Holds -->
                <button 
                    @click="setFilter('EXCEPTION')" 
                    type="button" 
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition group text-left"
                    title="Active Exceptions"
                >
                    <div class="w-5 h-5 flex items-center justify-center shrink-0 text-slate-400 group-hover:text-rose-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="whitespace-nowrap flex-1">Exceptions & Holds</span>
                    @if($openExceptions > 0)
                        <span x-show="sidebarExpanded" class="px-1.5 py-0.5 text-[10px] font-mono bg-rose-950 text-rose-300 font-bold rounded border border-rose-800 animate-pulse">
                            {{ $openExceptions }}
                        </span>
                    @endif
                </button>

                <!-- Notifications Alerts -->
                <a 
                    href="{{ route('notifications.index') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition group"
                    title="In-App Alerts"
                >
                    <div class="w-5 h-5 flex items-center justify-center shrink-0 text-slate-400 group-hover:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="whitespace-nowrap flex-1">Alerts & Dispatch</span>
                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span x-show="sidebarExpanded" class="px-1.5 py-0.5 text-[10px] font-mono bg-blue-600 text-white font-bold rounded-full">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                @if(auth()->check() && auth()->user()->role === 'admin')
                <!-- Audit Logs (Admin Only) -->
                <a 
                    href="{{ route('audit-logs.index') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition group"
                    title="Audit Logs"
                >
                    <div class="w-5 h-5 flex items-center justify-center shrink-0 text-slate-400 group-hover:text-purple-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="whitespace-nowrap flex-1">Enterprise Audit</span>
                </a>
                @endif

                <!-- Architecture & Specs -->
                <a 
                    href="{{ route('architecture') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition group"
                    title="System Architecture"
                >
                    <div class="w-5 h-5 flex items-center justify-center shrink-0 text-slate-400 group-hover:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="whitespace-nowrap flex-1">Architecture Docs</span>
                </a>
            </nav>

            <!-- Quick Corridor Metric Badges (Only in expanded sidebar) -->
            <div x-show="sidebarExpanded" x-transition.opacity class="mx-3 mt-4 p-3 rounded-xl bg-slate-900/80 border border-slate-800 text-[11px] space-y-2">
                <div class="flex items-center justify-between text-slate-400 font-mono text-[10px] uppercase font-bold">
                    <span>Corridor SLA Track</span>
                    <span class="text-emerald-400">Active</span>
                </div>
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">SEA-EU (12h)</span>
                        <span class="font-mono font-bold text-emerald-400">{{ $seaEuOnTimeRate }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 h-1 rounded-full overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $seaEuOnTimeRate }}%"></div>
                    </div>
                </div>
                <div class="space-y-1.5 text-xs pt-1">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Asia-NA (18h)</span>
                        <span class="font-mono font-bold text-amber-400">+{{ $asiaNaAvgDelay }}h avg</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section: User Profile & Controls -->
        <div class="p-3 border-t border-slate-800/80 bg-[#0E1522] space-y-2">
            <!-- Theme Toggle & Map Provider Badge -->
            <div class="flex items-center justify-between">
                <button 
                    id="theme-toggle"
                    onclick="toggleTheme()" 
                    type="button" 
                    class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors" 
                    title="Toggle Dark / Light theme"
                >
                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" />
                    </svg>
                </button>

                <div x-show="sidebarExpanded" class="flex items-center gap-1.5 text-[10px] text-slate-500 font-mono">
                    <span id="fleet-map-provider-badge">
                        @if(config('services.geoapify.key'))
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                            <span id="fleet-map-provider-text">Geoapify Maritime Vector</span>
                        @else
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span id="fleet-map-provider-text">Esri Maritime Canvas</span>
                        @endif
                    </span>
                </div>
            </div>

            <!-- Profile & Logout Bar -->
            @auth
            <div class="flex items-center justify-between pt-1">
                <div class="flex items-center gap-2 overflow-hidden">
                    <div class="w-7 h-7 rounded-lg bg-blue-600/30 border border-blue-500/40 text-cyan-300 font-bold flex items-center justify-center text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div x-show="sidebarExpanded" x-transition.opacity.duration.200ms class="flex flex-col overflow-hidden">
                        <span class="text-xs font-bold text-slate-200 truncate">{{ auth()->user()->name ?? 'User' }}</span>
                        <span class="text-[9px] uppercase tracking-wider text-slate-500 font-semibold">{{ auth()->user()->role ?? 'viewer' }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-950/40 transition-colors" title="Sign Out">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </aside>

    <!-- ========================================================================= -->
    <!-- 2. MAIN RADAR CANVAS & FLOATING OVERLAYS                                  -->
    <!-- ========================================================================= -->
    <main class="flex-1 h-full relative overflow-hidden flex flex-col">

        <!-- 2.1 TOP FLOATING SEARCH & FILTER HUD (MarineTraffic Style) -->
        <div class="absolute top-4 left-1/2 -translate-x-1/2 z-[1000] w-full max-w-2xl px-4 pointer-events-auto">
            <div class="bg-[#0B111A]/90 backdrop-blur-md border border-slate-700/70 shadow-2xl rounded-2xl p-2.5 transition-all">
                <!-- Search Input Row -->
                <div class="relative flex items-center">
                    <div class="absolute left-3 text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <input 
                        x-ref="searchInput"
                        x-model="searchQuery" 
                        @keydown.escape="searchQuery = ''"
                        @keydown.window.prevent.slash="$refs.searchInput.focus()"
                        type="text" 
                        placeholder="Search CargoTrace: Consignment #, Vessel, Port, Carrier... (Press '/' to focus)" 
                        class="w-full pl-9 pr-20 py-2 bg-slate-900/90 border border-slate-700/80 rounded-xl text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all font-sans"
                    />

                    <!-- Clear Search Button or Shortcut Badge -->
                    <div class="absolute right-2.5 flex items-center gap-1.5">
                        <button 
                            x-show="searchQuery.length > 0" 
                            @click="searchQuery = ''" 
                            type="button" 
                            class="p-1 rounded text-slate-400 hover:text-white"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                        <span class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-mono text-slate-500 bg-slate-800 rounded border border-slate-700">/</span>
                    </div>
                </div>

                <!-- Fast Filter Chips Row -->
                <div class="flex items-center gap-1.5 mt-2 overflow-x-auto pb-0.5 text-[11px] font-semibold scrollbar-none">
                    <button 
                        @click="setFilter('ALL')" 
                        :class="activeFilter === 'ALL' ? 'bg-cyan-500 text-slate-950 font-bold shadow-xs' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 border border-slate-700/60'"
                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap"
                    >
                        All Corridors ({{ count($dashboardMapRoutes) }})
                    </button>
                    <button 
                        @click="setFilter('IN_TRANSIT')" 
                        :class="activeFilter === 'IN_TRANSIT' ? 'bg-blue-500 text-white font-bold shadow-xs' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 border border-slate-700/60'"
                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                        In Transit ({{ $inTransit }})
                    </button>
                    <button 
                        @click="setFilter('DELAYED')" 
                        :class="activeFilter === 'DELAYED' ? 'bg-amber-500 text-slate-950 font-bold shadow-xs' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 border border-slate-700/60'"
                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        Delayed ({{ $delayed }})
                    </button>
                    <button 
                        @click="setFilter('EXCEPTION')" 
                        :class="activeFilter === 'EXCEPTION' ? 'bg-rose-500 text-white font-bold shadow-xs' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 border border-slate-700/60'"
                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-ping"></span>
                        Exceptions ({{ $openExceptions }})
                    </button>
                    <button 
                        @click="setFilter('SEA')" 
                        :class="activeFilter === 'SEA' ? 'bg-emerald-500 text-slate-950 font-bold shadow-xs' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 border border-slate-700/60'"
                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap"
                    >
                        Sea Cargo
                    </button>
                    <button 
                        @click="setFilter('AIR')" 
                        :class="activeFilter === 'AIR' ? 'bg-indigo-500 text-white font-bold shadow-xs' : 'bg-slate-800/80 text-slate-300 hover:bg-slate-700 border border-slate-700/60'"
                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap"
                    >
                        Air Freight
                    </button>
                </div>

                <!-- Instant Search Results Dropdown -->
                <div 
                    x-show="searchQuery.trim().length > 0 && matchingRoutes.length > 0" 
                    x-transition 
                    class="mt-2 pt-2 border-t border-slate-800 max-h-56 overflow-y-auto space-y-1"
                >
                    <template x-for="r in matchingRoutes" :key="r.id">
                        <div 
                            @click="selectShipment(r)" 
                            class="p-2 rounded-xl hover:bg-slate-800/90 cursor-pointer flex items-center justify-between transition-colors group"
                        >
                            <div class="flex items-center gap-2.5">
                                <span 
                                    :class="r.carrier.transport_mode === 'AIR' ? 'text-indigo-400 bg-indigo-950/60' : 'text-cyan-400 bg-cyan-950/60'"
                                    class="p-1 rounded-md text-[10px] font-mono font-bold"
                                    x-text="r.carrier.transport_mode"
                                ></span>
                                <div>
                                    <div class="font-mono font-bold text-xs text-white group-hover:text-cyan-300 transition-colors" x-text="r.number"></div>
                                    <div class="text-[10px] text-slate-400">
                                        <span x-text="r.origin.city"></span> &rarr; <span x-text="r.destination.city"></span>
                                        <span class="text-slate-500">&bull;</span>
                                        <span x-text="r.carrier.name"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span 
                                    :class="r.is_delayed ? 'text-amber-400 bg-amber-950/50 border-amber-800' : 'text-emerald-400 bg-emerald-950/50 border-emerald-800'"
                                    class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold border"
                                    x-text="r.status"
                                ></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- 2.2 RIGHT FLOATING GIS CONTROLS (MarineTraffic Style) -->
        <div class="absolute top-4 right-4 z-[1000] flex flex-col gap-2 pointer-events-auto">
            <!-- Basemap Layer Switcher Button -->
            <div x-data="{ open: false }" class="relative">
                <button 
                    @click="open = !open" 
                    type="button" 
                    class="w-10 h-10 rounded-xl bg-[#0B111A]/90 hover:bg-slate-800 text-slate-200 border border-slate-700/80 shadow-xl flex items-center justify-center transition-colors"
                    title="Change Map Tiles"
                >
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </button>

                <!-- Basemap Choices Popover -->
                <div 
                    x-show="open" 
                    @click.outside="open = false" 
                    x-transition 
                    class="absolute right-12 top-0 w-44 bg-[#0B111A]/95 backdrop-blur-md border border-slate-700/80 rounded-xl shadow-2xl p-2 space-y-1 text-xs font-semibold"
                >
                    <div class="text-[10px] uppercase font-bold text-slate-400 px-2 py-1">Nautical Basemap</div>
                    <button 
                        @click="switchTileLayer('esri-dark'); open = false" 
                        :class="activeLayer === 'esri-dark' ? 'text-cyan-400 bg-slate-800' : 'text-slate-300 hover:bg-slate-800/60'"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg flex items-center justify-between"
                    >
                        <span>Esri Dark Canvas</span>
                        <span x-show="activeLayer === 'esri-dark'" class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                    </button>
                    <button 
                        @click="switchTileLayer('esri-light'); open = false" 
                        :class="activeLayer === 'esri-light' ? 'text-cyan-400 bg-slate-800' : 'text-slate-300 hover:bg-slate-800/60'"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg flex items-center justify-between"
                    >
                        <span>Esri Light Canvas</span>
                        <span x-show="activeLayer === 'esri-light'" class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                    </button>
                    @if(config('services.geoapify.key'))
                    <button 
                        @click="switchTileLayer('geoapify'); open = false" 
                        :class="activeLayer === 'geoapify' ? 'text-cyan-400 bg-slate-800' : 'text-slate-300 hover:bg-slate-800/60'"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg flex items-center justify-between"
                    >
                        <span>Geoapify Maritime</span>
                        <span x-show="activeLayer === 'geoapify'" class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                    </button>
                    @endif
                    <button 
                        @click="switchTileLayer('satellite'); open = false" 
                        :class="activeLayer === 'satellite' ? 'text-cyan-400 bg-slate-800' : 'text-slate-300 hover:bg-slate-800/60'"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg flex items-center justify-between"
                    >
                        <span>World Satellite</span>
                        <span x-show="activeLayer === 'satellite'" class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                    </button>
                    <button 
                        @click="switchTileLayer('streets'); open = false" 
                        :class="activeLayer === 'streets' ? 'text-cyan-400 bg-slate-800' : 'text-slate-300 hover:bg-slate-800/60'"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg flex items-center justify-between"
                    >
                        <span>OpenStreetMap</span>
                        <span x-show="activeLayer === 'streets'" class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                    </button>
                </div>
            </div>

            <!-- Recenter / Fit All Corridors -->
            <button 
                @click="fitAllCorridors()" 
                type="button" 
                class="w-10 h-10 rounded-xl bg-[#0B111A]/90 hover:bg-slate-800 text-slate-200 border border-slate-700/80 shadow-xl flex items-center justify-center transition-colors"
                title="Fit All Fleet Corridors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
            </button>

            <!-- Toggle Route Polylines -->
            <button 
                @click="togglePolylines()" 
                type="button" 
                :class="showPolylines ? 'text-cyan-400' : 'text-slate-500'"
                class="w-10 h-10 rounded-xl bg-[#0B111A]/90 hover:bg-slate-800 border border-slate-700/80 shadow-xl flex items-center justify-center transition-colors"
                title="Toggle Route Polylines"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </button>

            <!-- Zoom Controls -->
            <div class="flex flex-col bg-[#0B111A]/90 border border-slate-700/80 rounded-xl shadow-xl overflow-hidden">
                <button 
                    @click="zoomIn()" 
                    type="button" 
                    class="w-10 h-10 hover:bg-slate-800 text-slate-200 flex items-center justify-center text-lg font-bold border-b border-slate-800 transition-colors"
                    title="Zoom In"
                >
                    +
                </button>
                <button 
                    @click="zoomOut()" 
                    type="button" 
                    class="w-10 h-10 hover:bg-slate-800 text-slate-200 flex items-center justify-center text-lg font-bold transition-colors"
                    title="Zoom Out"
                >
                    &minus;
                </button>
            </div>
        </div>

        <!-- 2.3 LEAFLET MAP CONTAINER -->
        <div id="marine-radar-canvas" class="w-full h-full z-0 bg-[#070B12]"></div>

        <!-- 2.4 BOTTOM STATUS BAR (MarineTraffic Coordinate & Vessel Readout) -->
        <div class="absolute bottom-3 right-4 z-[1000] flex items-center gap-2 pointer-events-auto">
            <!-- Live Mouse Cursor Coordinates -->
            <div class="px-3 py-1.5 rounded-xl bg-[#0B111A]/90 backdrop-blur-md border border-slate-700/80 shadow-xl font-mono text-[11px] text-slate-300 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                <span x-text="cursorCoords">00° 00.00' N, 000° 00.00' E</span>
            </div>

            <!-- Active Fleet Count Pill -->
            <div class="px-3 py-1.5 rounded-xl bg-[#0B111A]/90 backdrop-blur-md border border-slate-700/80 shadow-xl font-mono text-[11px] text-cyan-300 font-bold flex items-center gap-1.5">
                <span class="text-slate-400">RADAR:</span>
                <span x-text="displayedRoutesCount"></span>
                <span class="text-[10px] text-slate-400">TRAILS</span>
            </div>
        </div>

        <!-- 2.5 FLOATING COLLAPSIBLE SLA & TELEMETRY HUD (Bottom Left) -->
        <div class="absolute bottom-3 left-4 z-[1000] pointer-events-auto max-w-xl transition-all">
            <!-- Minimized Trigger Pill -->
            <button 
                @click="toggleHud()" 
                type="button" 
                class="px-3.5 py-2 rounded-xl bg-[#0B111A]/90 backdrop-blur-md border border-slate-700/80 shadow-2xl hover:bg-slate-800/90 text-xs font-semibold text-slate-200 flex items-center gap-3 transition-colors"
            >
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-bold tracking-tight">Telemetry & SLA HUD</span>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-mono text-slate-400">
                    <span class="px-1.5 py-0.5 rounded bg-blue-950/60 text-blue-300 border border-blue-900">In Transit: {{ $inTransit }}</span>
                    <span class="px-1.5 py-0.5 rounded bg-amber-950/60 text-amber-300 border border-amber-900">Delayed: {{ $delayed }}</span>
                    <span class="px-1.5 py-0.5 rounded bg-rose-950/60 text-rose-300 border border-rose-900">Exceptions: {{ $openExceptions }}</span>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="isHudExpanded ? 'rotate-180' : 'rotate-0'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>

            <!-- Expanded Telemetry Panel -->
            <div 
                x-show="isHudExpanded" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                class="mt-2 p-4 rounded-2xl bg-[#0B111A]/95 backdrop-blur-xl border border-slate-700/90 shadow-2xl space-y-4 w-[380px] sm:w-[480px]"
            >
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-white">Corridor Performance & Delay Metrics</h3>
                        <p class="text-[10px] text-slate-400">Deterministic SLA tracking across intercontinental lanes</p>
                    </div>
                    <button @click="isHudExpanded = false" class="text-slate-400 hover:text-white text-xs">&times;</button>
                </div>

                <!-- 3 Corridor KPI Badges -->
                <div class="grid grid-cols-3 gap-2">
                    <!-- SEA-EU -->
                    <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800">
                        <div class="text-[9px] font-bold text-slate-400 uppercase">SEA &rarr; EU (12h)</div>
                        <div class="text-lg font-black text-emerald-400 mt-1 font-mono">{{ $seaEuOnTimeRate }}%</div>
                        <div class="text-[9px] text-slate-500">On-Time Target</div>
                    </div>
                    <!-- ASIA-NA -->
                    <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800">
                        <div class="text-[9px] font-bold text-slate-400 uppercase">Asia &rarr; NA (18h)</div>
                        <div class="text-lg font-black text-amber-400 mt-1 font-mono">+{{ $asiaNaAvgDelay }}h</div>
                        <div class="text-[9px] text-slate-500">Avg Corridor Delay</div>
                    </div>
                    <!-- SEA-AU -->
                    <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800">
                        <div class="text-[9px] font-bold text-slate-400 uppercase">SEA &rarr; AU (24h)</div>
                        <div class="text-lg font-black text-cyan-400 mt-1 font-mono">{{ $seaAuAvgResolution }}h</div>
                        <div class="text-[9px] text-slate-500">Resolution Speed</div>
                    </div>
                </div>

                <!-- Status & Carrier Volume Charts -->
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-800">
                    <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Status Lifecycle</span>
                        <div class="h-28 relative">
                            <canvas id="lifecycleStatusChart"></canvas>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Carrier Breakdown</span>
                        <div class="h-28 relative">
                            <canvas id="carrierVolumeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Chart Toggle View -->
                <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-xs">
                    <span class="text-slate-400">Lifecycle: <strong>{{ $inTransit }}</strong> Active / <strong>{{ $totalShipments }}</strong> Total</span>
                    <a href="{{ route('shipments.index') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1 text-[11px]">
                        <span>Full Directory Table</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. SLIDE-OVER SHIPMENT INTELLIGENCE DRAWER (Right Edge)                    -->
        <!-- ========================================================================= -->
        <div 
            :class="isDrawerOpen ? 'translate-x-0' : 'translate-x-full'" 
            class="fixed sm:absolute top-0 right-0 h-full w-full sm:w-[420px] z-[1010] bg-[#0B111A]/95 backdrop-blur-xl border-l border-slate-800 shadow-2xl flex flex-col justify-between transition-transform duration-300 ease-out pointer-events-auto overflow-hidden"
        >
            <template x-if="selectedShipment">
                <div class="h-full flex flex-col justify-between">
                    <!-- Drawer Header -->
                    <div class="p-5 border-b border-slate-800 bg-[#0E1522]">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span 
                                    :class="selectedShipment.carrier.transport_mode === 'AIR' ? 'bg-indigo-950 text-indigo-300 border-indigo-800' : 'bg-cyan-950 text-cyan-300 border-cyan-800'"
                                    class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider border"
                                    x-text="selectedShipment.carrier.transport_mode + ' FREIGHT'"
                                ></span>
                                <span 
                                    :class="selectedShipment.is_delayed ? 'bg-amber-950 text-amber-300 border-amber-800' : 'bg-emerald-950 text-emerald-300 border-emerald-800'"
                                    class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase border"
                                    x-text="selectedShipment.status"
                                ></span>
                            </div>
                            <button 
                                @click="closeDrawer()" 
                                type="button" 
                                class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <h2 class="text-lg font-black font-mono text-white tracking-tight flex items-center gap-2">
                            <span x-text="selectedShipment.number"></span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5 font-medium">
                            Carrier: <span class="text-slate-200 font-semibold" x-text="selectedShipment.carrier.name"></span>
                            (<span class="font-mono text-cyan-400" x-text="selectedShipment.carrier.code"></span>)
                        </p>
                    </div>

                    <!-- Drawer Scrollable Body -->
                    <div class="flex-1 overflow-y-auto p-5 space-y-5 text-xs text-slate-300">
                        <!-- Route Corridor Visual Bar -->
                        <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 space-y-3">
                            <div class="flex items-center justify-between text-[11px]">
                                <div>
                                    <div class="text-[9px] uppercase font-bold text-slate-500">Origin Port</div>
                                    <div class="font-bold text-white text-sm" x-text="selectedShipment.origin.city"></div>
                                    <div class="text-[10px] text-slate-400 font-mono" x-text="selectedShipment.origin.code"></div>
                                </div>
                                <div class="text-center px-2">
                                    <div class="text-[9px] font-mono font-bold text-cyan-400 uppercase tracking-widest">
                                        <span x-text="selectedShipment.progress_percent + '%'"></span>
                                    </div>
                                    <svg class="w-5 h-5 text-cyan-400 mx-auto animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <div class="text-[9px] uppercase font-bold text-slate-500">Destination</div>
                                    <div class="font-bold text-white text-sm" x-text="selectedShipment.destination.city"></div>
                                    <div class="text-[10px] text-slate-400 font-mono" x-text="selectedShipment.destination.code"></div>
                                </div>
                            </div>

                            <!-- Progress Track -->
                            <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div 
                                    :class="selectedShipment.is_delayed ? 'bg-amber-500' : 'bg-cyan-400'"
                                    class="h-full rounded-full transition-all duration-500" 
                                    :style="'width: ' + selectedShipment.progress_percent + '%'"
                                ></div>
                            </div>

                            <div class="flex justify-between items-center text-[10px] text-slate-400 font-mono">
                                <span>Dep: <span class="text-slate-200" x-text="selectedShipment.expected_departure"></span></span>
                                <span>ETA: <span class="text-slate-200" x-text="selectedShipment.estimated_arrival"></span></span>
                            </div>
                        </div>

                        <!-- Real-Time Telemetry & Heading -->
                        <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800 grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Current Waypoint</span>
                                <span class="font-semibold text-white text-xs mt-0.5 block" x-text="selectedShipment.current.name"></span>
                                <span class="text-[10px] text-slate-500 font-mono" x-text="selectedShipment.current.lat.toFixed(4) + ', ' + selectedShipment.current.lng.toFixed(4)"></span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Course Heading</span>
                                <span class="font-mono font-bold text-cyan-400 text-sm mt-0.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 transition-transform" :style="'transform: rotate(' + selectedShipment.heading + 'deg)'" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z" clip-rule="evenodd" />
                                    </svg>
                                    <span x-text="selectedShipment.heading + '°'"></span>
                                </span>
                                <span class="text-[10px] text-slate-500">True Azimuth</span>
                            </div>
                        </div>

                        <!-- Delay Alert (If delayed) -->
                        <template x-if="selectedShipment.is_delayed">
                            <div class="p-3.5 rounded-xl bg-amber-950/40 border border-amber-800/80 text-amber-200 space-y-1">
                                <div class="flex items-center gap-2 text-xs font-bold text-amber-300 uppercase">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Delayed +<span x-text="selectedShipment.delay_hours"></span>h (<span x-text="selectedShipment.delay_severity"></span>)</span>
                                </div>
                                <p class="text-[11px] text-amber-300/80 leading-relaxed" x-text="selectedShipment.delay_reason || 'Corridor transit target exceeded.'"></p>
                            </div>
                        </template>

                        <!-- Active Exceptions Warning Card -->
                        <template x-if="selectedShipment.active_exceptions && selectedShipment.active_exceptions.length > 0">
                            <div class="p-3.5 rounded-xl bg-rose-950/40 border border-rose-800/80 text-rose-200 space-y-2">
                                <div class="flex items-center gap-2 text-xs font-bold text-rose-300 uppercase">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                    <span>Active Exception Hold</span>
                                </div>
                                <template x-for="exc in selectedShipment.active_exceptions" :key="exc.id">
                                    <div class="text-[11px] space-y-0.5 border-t border-rose-900/60 pt-1.5 first:border-0 first:pt-0">
                                        <div class="font-bold text-rose-200" x-text="exc.type + ' (' + exc.severity + ')'"></div>
                                        <p class="text-rose-300/80" x-text="exc.description"></p>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Cargo Manifest Summary -->
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Cargo Manifest</h4>
                            <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800 space-y-1.5 text-xs">
                                <div class="font-semibold text-white" x-text="selectedShipment.cargo_type"></div>
                                <div class="text-slate-400 text-[11px] flex justify-between">
                                    <span>Client: <strong class="text-slate-200" x-text="selectedShipment.customer_name"></strong></span>
                                    <span>Items: <strong class="text-slate-200 font-mono" x-text="selectedShipment.items_count"></strong></span>
                                </div>
                                <div class="text-slate-400 text-[11px] flex justify-between font-mono pt-1 border-t border-slate-800/60">
                                    <span>Weight: <strong class="text-slate-200" x-text="selectedShipment.total_weight_kg.toLocaleString() + ' kg'"></strong></span>
                                    <span>Volume: <strong class="text-slate-200" x-text="selectedShipment.total_volume_cbm + ' CBM'"></strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Waypoints Sequence Timeline -->
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Corridor Checkpoint Trajectory</h4>
                            <div class="space-y-2 pl-2 border-l border-slate-800">
                                <template x-for="(wp, idx) in selectedShipment.waypoints" :key="idx">
                                    <div class="relative pl-4 text-xs">
                                        <div 
                                            :class="wp.type === 'ORIGIN' ? 'bg-emerald-400 ring-emerald-950' : (wp.type === 'DESTINATION' ? 'bg-cyan-400 ring-cyan-950' : (wp.is_exception ? 'bg-rose-400 ring-rose-950 animate-ping' : 'bg-slate-500 ring-slate-900'))"
                                            class="absolute -left-[17px] top-1 w-2.5 h-2.5 rounded-full ring-4"
                                        ></div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-white text-[11px]" x-text="wp.name"></span>
                                            <span class="text-[9px] font-mono text-slate-500" x-text="wp.time"></span>
                                        </div>
                                        <div class="text-[10px] text-slate-400" x-text="wp.city + (wp.country ? ', ' + wp.country : '')"></div>
                                        <template x-if="wp.description">
                                            <p class="text-[10px] text-slate-500 mt-0.5 italic" x-text="wp.description"></p>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Drawer Footer Actions -->
                    <div class="p-4 border-t border-slate-800 bg-[#0E1522] flex items-center gap-3">
                        <a 
                            :href="'/shipments/' + selectedShipment.id" 
                            class="flex-1 text-center py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition-colors flex items-center justify-center gap-2"
                        >
                            <span>Open Consignment Dossier</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </main>
</div>

<!-- ========================================================================= -->
<!-- 4. RADAR CLIENT CONTROLLER (Leaflet & Alpine.js Logic)                     -->
<!-- ========================================================================= -->
<script>
function marineRadar() {
    return {
        // Alpine States
        sidebarExpanded: true,
        searchQuery: '',
        activeFilter: 'ALL',
        activeLayer: 'esri-dark',
        geoapifyKey: @json(config('services.geoapify.key', '')),
        showPolylines: true,
        isDrawerOpen: false,
        isHudExpanded: false,
        selectedShipment: null,
        cursorCoords: "00° 00.00' N, 000° 00.00' E",

        // Data from Controller
        routesData: @json($dashboardMapRoutes ?? []),

        // Leaflet references
        map: null,
        currentTileLayer: null,
        markersLayerGroup: null,
        polylinesLayerGroup: null,

        // Computed Filtered List
        get matchingRoutes() {
            let list = this.routesData;

            // Apply quick filter category
            if (this.activeFilter === 'IN_TRANSIT') {
                list = list.filter(r => r.status === 'IN_TRANSIT');
            } else if (this.activeFilter === 'DELAYED') {
                list = list.filter(r => r.is_delayed);
            } else if (this.activeFilter === 'EXCEPTION') {
                list = list.filter(r => r.active_exceptions && r.active_exceptions.length > 0);
            } else if (this.activeFilter === 'SEA') {
                list = list.filter(r => r.carrier.transport_mode === 'SEA');
            } else if (this.activeFilter === 'AIR') {
                list = list.filter(r => r.carrier.transport_mode === 'AIR');
            }

            // Apply search query
            const q = this.searchQuery.trim().toLowerCase();
            if (q.length > 0) {
                list = list.filter(r => {
                    return (r.number && r.number.toLowerCase().includes(q)) ||
                           (r.carrier.name && r.carrier.name.toLowerCase().includes(q)) ||
                           (r.origin.name && r.origin.name.toLowerCase().includes(q)) ||
                           (r.origin.city && r.origin.city.toLowerCase().includes(q)) ||
                           (r.destination.name && r.destination.name.toLowerCase().includes(q)) ||
                           (r.destination.city && r.destination.city.toLowerCase().includes(q)) ||
                           (r.cargo_type && r.cargo_type.toLowerCase().includes(q));
                });
            }

            return list;
        },

        get displayedRoutesCount() {
            return this.matchingRoutes.length;
        },

        initRadar() {
            this.$nextTick(() => {
                this.setupMap();
                this.renderCorridors(false);
                this.initCharts();
            });
        },

        initCharts() {
            const ctxStatus = document.getElementById('lifecycleStatusChart');
            if (ctxStatus && typeof Chart !== 'undefined') {
                const statusData = @json($statusBreakdown ?? []);
                new Chart(ctxStatus, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(statusData),
                        datasets: [{
                            data: Object.values(statusData),
                            backgroundColor: ['#64748b', '#0ea5e9', '#6366f1', '#3b82f6', '#10b981'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
            }

            const ctxCarrier = document.getElementById('carrierVolumeChart');
            if (ctxCarrier && typeof Chart !== 'undefined') {
                const carrierData = @json($carrierVolumeBreakdown ?? ['labels' => [], 'counts' => []]);
                new Chart(ctxCarrier, {
                    type: 'doughnut',
                    data: {
                        labels: carrierData.labels || [],
                        datasets: [{
                            data: carrierData.counts || [],
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });
            }
        },

        setupMap() {
            const canvasEl = document.getElementById('marine-radar-canvas');
            if (!canvasEl || typeof L === 'undefined') return;

            const worldBounds = [[-65, -180], [80, 180]];

            // Initialize Leaflet Map
            this.map = L.map('marine-radar-canvas', {
                center: [18, 65],
                zoom: 3,
                minZoom: 2,
                maxBounds: worldBounds,
                maxBoundsViscosity: 1.0,
                worldCopyJump: false,
                zoomControl: false, // Custom controls used
                attributionControl: false
            });

            // Dynamically lock minimum zoom to widescreen container width to eliminate margins
            const minFillZoom = Math.max(3, this.map.getBoundsZoom(worldBounds, true));
            this.map.setMinZoom(minFillZoom);
            this.map.setView([18, 65], minFillZoom);

            this.markersLayerGroup = L.layerGroup().addTo(this.map);
            this.polylinesLayerGroup = L.layerGroup().addTo(this.map);

            // Apply base layer
            this.switchTileLayer(this.activeLayer);

            // Invalidate size to eliminate gray/black margins after container render
            setTimeout(() => {
                this.map.invalidateSize();
                const refreshedMinZoom = Math.max(3, this.map.getBoundsZoom(worldBounds, true));
                this.map.setMinZoom(refreshedMinZoom);
            }, 200);

            // Live Cursor Coordinate Tracker (MarineTraffic style)
            this.map.on('mousemove', (e) => {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                const latDir = lat >= 0 ? 'N' : 'S';
                const lngDir = lng >= 0 ? 'E' : 'W';
                const latDeg = Math.floor(Math.abs(lat));
                const latMin = ((Math.abs(lat) - latDeg) * 60).toFixed(2);
                const lngDeg = Math.floor(Math.abs(lng));
                const lngMin = ((Math.abs(lng) - lngDeg) * 60).toFixed(2);

                this.cursorCoords = `${latDeg}° ${latMin}' ${latDir}, ${lngDeg}° ${lngMin}' ${lngDir}`;
            });

            // Auto resize on window change
            window.addEventListener('resize', () => {
                this.map.invalidateSize();
                const newMinZoom = Math.max(3, this.map.getBoundsZoom(worldBounds, true));
                this.map.setMinZoom(newMinZoom);
            });
        },

        switchTileLayer(type) {
            this.activeLayer = type;
            if (!this.map) return;

            if (this.currentTileLayer) {
                this.map.removeLayer(this.currentTileLayer);
            }

            const worldBounds = [[-65, -180], [80, 180]];
            let tileUrl = '';
            let tileOpts = {
                maxZoom: 18,
                noWrap: true,
                bounds: worldBounds,
                attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, OpenStreetMap'
            };

            const geoKey = (this.geoapifyKey || '').trim();
            const isDark = document.documentElement.classList.contains('dark') || true;

            if (geoKey.length > 0) {
                tileUrl = isDark
                    ? `https://maps.geoapify.com/v1/tile/dark-matter/{z}/{x}/{y}.png?apiKey=${geoKey}`
                    : `https://maps.geoapify.com/v1/tile/positron/{z}/{x}/{y}.png?apiKey=${geoKey}`;
                tileOpts = {
                    maxZoom: 20,
                    noWrap: true,
                    bounds: worldBounds,
                    attribution: 'Powered by <a href="https://www.geoapify.com/" target="_blank">Geoapify</a> | &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
                };
            } else if (type === 'esri-light') {
                tileUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}';
                tileOpts.subdomains = ['server'];
                tileOpts.noWrap = true;
                tileOpts.bounds = worldBounds;
                tileOpts.attribution = 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, OpenStreetMap';
            } else if (type === 'satellite') {
                tileUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
                tileOpts.subdomains = ['server'];
                tileOpts.noWrap = true;
                tileOpts.bounds = worldBounds;
                tileOpts.attribution = 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics';
            } else if (type === 'streets') {
                tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                tileOpts.subdomains = 'abc';
                tileOpts.noWrap = true;
                tileOpts.bounds = worldBounds;
                tileOpts.attribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
            } else {
                // Default: Esri World Dark Gray Base (Fallback for Geoapify)
                tileUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}';
                tileOpts.subdomains = ['server'];
                tileOpts.noWrap = true;
                tileOpts.bounds = worldBounds;
                tileOpts.attribution = 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, OpenStreetMap';
                
            }
            tileOpts.maxNativeZoom = 16;
            tileOpts.maxZoom = 18;           
            this.currentTileLayer = L.tileLayer(tileUrl, tileOpts).addTo(this.map);

            // Handle tile error fallback to Esri
            this.currentTileLayer.on('tileerror', () => {
                if (this.geoapifyKey) {
                    console.warn('Geoapify tile error. Falling back to Esri Dark Canvas.');
                    this.geoapifyKey = '';
                    this.switchTileLayer('esri-dark');
                }
            });
        },

        renderCorridors() {
            if (!this.map || !this.markersLayerGroup || !this.polylinesLayerGroup) return;

            this.markersLayerGroup.clearLayers();
            this.polylinesLayerGroup.clearLayers();

            const routes = this.matchingRoutes;
            const allBoundsPoints = [];

            routes.forEach((route) => {
                const isAir = route.carrier.transport_mode === 'AIR';
                const hasException = route.active_exceptions && route.active_exceptions.length > 0;

                // Color palette
                let markerColor = '#10B981'; // Emerald for On-Time
                if (hasException) {
                    markerColor = '#F43F5E'; // Rose for Exception Hold
                } else if (route.is_delayed) {
                    markerColor = '#F59E0B'; // Amber for Delayed
                } else if (isAir) {
                    markerColor = '#6366F1'; // Indigo for Air
                }

                // Collect trajectory points
                const trajectoryCoords = [];
                if (route.waypoints && route.waypoints.length > 0) {
                    route.waypoints.forEach(wp => {
                        trajectoryCoords.push([wp.lat, wp.lng]);
                        allBoundsPoints.push([wp.lat, wp.lng]);
                    });
                } else {
                    trajectoryCoords.push([route.origin.lat, route.origin.lng]);
                    trajectoryCoords.push([route.current.lat, route.current.lng]);
                    trajectoryCoords.push([route.destination.lat, route.destination.lng]);
                    allBoundsPoints.push([route.origin.lat, route.origin.lng]);
                    allBoundsPoints.push([route.destination.lat, route.destination.lng]);
                }

                // 1. Draw Polyline Corridor Trails
                if (this.showPolylines && trajectoryCoords.length >= 2) {
                    // Glow background polyline
                    L.polyline(trajectoryCoords, {
                        color: markerColor,
                        weight: 5,
                        opacity: 0.25,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(this.polylinesLayerGroup);

                    // Crisp foreground polyline
                    const mainPolyline = L.polyline(trajectoryCoords, {
                        color: markerColor,
                        weight: 2,
                        opacity: 0.85,
                        dashArray: route.status === 'DELIVERED' ? null : '6, 6',
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(this.polylinesLayerGroup);

                    mainPolyline.on('click', () => {
                        this.selectShipment(route);
                    });
                }

                // 2. Draw Origin Port Marker
                const originIcon = L.divIcon({
                    className: 'marine-origin-port',
                    html: `
                        <div class="w-4 h-4 rounded-full bg-emerald-500/90 border-2 border-[#0B111A] shadow-md flex items-center justify-center cursor-pointer hover:scale-125 transition-transform" title="Origin: ${route.origin.name}">
                            <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                        </div>
                    `,
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                });
                const originMarker = L.marker([route.origin.lat, route.origin.lng], { icon: originIcon });
                originMarker.on('click', () => this.selectShipment(route));
                originMarker.addTo(this.markersLayerGroup);

                // 3. Draw Destination Port Marker
                const destIcon = L.divIcon({
                    className: 'marine-dest-port',
                    html: `
                        <div class="w-4 h-4 rounded-full bg-cyan-400/90 border-2 border-[#0B111A] shadow-md flex items-center justify-center cursor-pointer hover:scale-125 transition-transform" title="Destination: ${route.destination.name}">
                            <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                        </div>
                    `,
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                });
                const destMarker = L.marker([route.destination.lat, route.destination.lng], { icon: destIcon });
                destMarker.on('click', () => this.selectShipment(route));
                destMarker.addTo(this.markersLayerGroup);

                // 4. Directional Vessel / Flight Marker (MarineTraffic Arrowhead / Boat hull)
                const headingAngle = route.heading || 0;
                const vesselSvg = isAir 
                    ? `<svg viewBox="0 0 24 24" width="28" height="28" style="fill: ${markerColor};"><path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z" stroke="#070B12" stroke-width="1"/></svg>`
                    : `<svg viewBox="0 0 24 32" width="26" height="32" style="fill: ${markerColor};"><path d="M12 2 C16 7 20 14 20 26 C20 29 16 30 12 30 C8 30 4 29 4 26 C4 14 8 7 12 2 Z" stroke="#070B12" stroke-width="1.5" stroke-linejoin="round"/><circle cx="12" cy="18" r="2.5" fill="#ffffff" stroke="#070B12" stroke-width="1"/></svg>`;

                const pulseRing = hasException 
                    ? `<div class="absolute -inset-2.5 rounded-full bg-rose-500/40 animate-ping"></div>`
                    : (route.is_delayed ? `<div class="absolute -inset-1.5 rounded-full bg-amber-500/30 animate-pulse"></div>` : '');

                const vesselIcon = L.divIcon({
                    className: 'marine-vessel-marker',
                    html: `
                        <div class="relative flex items-center justify-center cursor-pointer group" style="width: 32px; height: 32px;">
                            ${pulseRing}
                            <div class="transition-transform duration-300 drop-shadow-xl hover:scale-125" style="transform: rotate(${headingAngle}deg);">
                                ${vesselSvg}
                            </div>
                            <div class="absolute -bottom-5 left-1/2 -translate-x-1/2 whitespace-nowrap px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-[#0B111A]/95 text-cyan-300 border border-slate-700 shadow-xl opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                ${route.number} &bull; ${route.carrier.code}
                            </div>
                        </div>
                    `,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                const vesselMarker = L.marker([route.current.lat, route.current.lng], { icon: vesselIcon });
                vesselMarker.on('click', () => {
                    this.selectShipment(route);
                });
                vesselMarker.addTo(this.markersLayerGroup);
            });

            // Adjust view to fit all bounds if initialized
            if (allBoundsPoints.length > 0) {
                const bounds = L.latLngBounds(allBoundsPoints);
                this.map.fitBounds(bounds, { padding: [80, 80], maxZoom: 6 });
            }
        },

        setFilter(filterKey) {
            this.activeFilter = filterKey;
            this.renderCorridors();
        },

        selectShipment(route) {
            this.selectedShipment = route;
            this.isDrawerOpen = true;

            // Pan and fly to current vessel position with smooth animation
            if (this.map && route.current) {
                this.map.flyTo([route.current.lat, route.current.lng], 6, {
                    animate: true,
                    duration: 1.2
                });
            }
        },

        closeDrawer() {
            this.isDrawerOpen = false;
        },

        toggleHud() {
            this.isHudExpanded = !this.isHudExpanded;
        },

        fitAllCorridors() {
            this.renderCorridors();
        },

        togglePolylines() {
            this.showPolylines = !this.showPolylines;
            this.renderCorridors();
        },

        zoomIn() {
            if (this.map) this.map.zoomIn();
        },

        zoomOut() {
            if (this.map) this.map.zoomOut();
        }
    }
}
</script>

<style>
/* Custom Leaflet Dark Nautical Theme Adjustments */
.leaflet-container {
    background-color: #070B12 !important;
    font-family: inherit;
}
.marine-vessel-marker, .marine-origin-port, .marine-dest-port {
    background: transparent !important;
    border: none !important;
}
</style>
@endsection
