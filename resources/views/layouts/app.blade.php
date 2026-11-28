<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Corridex — Multi-Corridor Freight Visibility Engine</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-icon.png') }}">

    <!-- Pre-load blocking script to prevent Flash of Unstyled Content (FOUC) -->
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'dark';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();

        window.updateThemeIcon = function () {
            const isDark = document.documentElement.classList.contains('dark');
            const lightIcon = document.getElementById('theme-toggle-light-icon');
            const darkIcon = document.getElementById('theme-toggle-dark-icon');
            if (lightIcon && darkIcon) {
                if (isDark) {
                    lightIcon.classList.remove('hidden');
                    darkIcon.classList.add('hidden');
                } else {
                    lightIcon.classList.add('hidden');
                    darkIcon.classList.remove('hidden');
                }
            }
        };

        window.toggleTheme = function () {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDark } }));
            window.updateThemeIcon();
        };

        document.addEventListener('DOMContentLoaded', window.updateThemeIcon);
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
                        },
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Leaflet & Chart.js Resources -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Alpine.js Declarative UI Engine -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        @media print {
            nav, footer, .no-print, button, form:not(.printable-form), .operator-panel {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .print-only {
                display: block !important;
            }
            .card, div {
                box-shadow: none !important;
                border-color: #e2e8f0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 dark:bg-[#070B12] dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-200">
@hasSection('full_viewport')
    @yield('full_viewport')
@else
    <!-- Top Navigation Bar -->
    <nav class="bg-white/90 dark:bg-[#111827]/90 backdrop-blur border-b border-slate-200 dark:border-slate-800 sticky top-0 z-50 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Brand Logo & Navigation Links -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-base font-bold tracking-tight flex items-center gap-2.5 text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition group">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-600 text-white shadow-sm p-1.5 ring-1 ring-blue-500/30 group-hover:bg-blue-500 transition">
                            <img src="{{ asset('images/logo-white.png') }}" alt="CX Logo" class="w-full h-auto object-contain">
                        </div>
                        <div class="flex flex-col">
                            <span class="font-extrabold leading-none tracking-tight">CORRIDEX</span>
                            <span class="text-[9px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Freight Visibility Engine</span>
                        </div>
                    </a>

                    <div class="hidden md:flex space-x-1 text-xs font-semibold">
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-100 dark:bg-slate-800 text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/60 dark:hover:bg-slate-800/60' }}">
                            Operational Dashboard
                        </a>
                        <a href="{{ route('shipments.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('shipments.*') ? 'bg-slate-100 dark:bg-slate-800 text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/60 dark:hover:bg-slate-800/60' }}">
                            Shipments & Filters
                        </a>
                        @if(auth()->check() && auth()->user()->role === 'admin')
                        <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('audit-logs.*') ? 'bg-slate-100 dark:bg-slate-800 text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/60 dark:hover:bg-slate-800/60' }}">
                            Audit Logs
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Right Controls: Theme Switcher, Notifications, Role Badge & Logout -->
                <div class="flex items-center space-x-3">
                    <!-- Dual-Theme Switcher Toggle Button -->
                    <button id="theme-toggle" type="button" onclick="toggleTheme()" aria-label="Toggle visual theme" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/80 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/30" title="Toggle Light / Dark mode">
                        <!-- Sun Icon (shown in dark mode) -->
                        <svg id="theme-toggle-light-icon" class="hidden w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                        <!-- Moon Icon (shown in light mode) -->
                        <svg id="theme-toggle-dark-icon" class="hidden w-4 h-4 text-slate-600 dark:text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>

                    @auth
                    <!-- Notifications Feed Link -->
                    <a href="{{ route('notifications.index') }}" class="relative p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-100/80 dark:bg-slate-800/80 transition-colors" title="In-App Alerts">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-bold leading-none text-white bg-red-600 rounded-full">
                                {{ $unreadNotificationsCount }}
                            </span>
                        @endif
                    </a>

                    <!-- User Role Badge -->
                    @php
                        $role = auth()->user()->role ?? 'guest';
                        $roleBadgeColor = match($role) {
                            'admin' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800/80',
                            'operator' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800/80',
                            default => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/80',
                        };
                    @endphp
                    <div class="hidden sm:flex flex-col items-end text-xs">
                        <span class="font-bold text-slate-800 dark:text-slate-200 leading-tight">{{ auth()->user()->name ?? 'Guest' }}</span>
                        <span class="px-2 py-0.5 mt-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $roleBadgeColor }}">
                            Role: {{ $role }}
                        </span>
                    </div>

                    <!-- Sign Out Form -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 dark:bg-slate-800 dark:hover:bg-red-950/40 dark:text-slate-300 dark:hover:text-red-400 transition-colors">
                            Sign Out
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors">
                        Sign In
                    </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Strip -->
        <div class="md:hidden border-t border-slate-200 dark:border-slate-800 px-4 py-2 flex justify-around text-xs font-semibold bg-white/95 dark:bg-[#111827]/95">
            <a href="{{ route('dashboard') }}" class="py-1 {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">Dashboard</a>
            <a href="{{ route('shipments.index') }}" class="py-1 {{ request()->routeIs('shipments.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">Shipments</a>
            @if(auth()->check() && auth()->user()->role === 'admin')
            <a href="{{ route('audit-logs.index') }}" class="py-1 {{ request()->routeIs('audit-logs.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">Audit Logs</a>
            @endif
            @auth
            <a href="{{ route('notifications.index') }}" class="py-1 text-slate-500 dark:text-slate-400">Alerts ({{ $unreadNotificationsCount ?? 0 }})</a>
            @endauth
        </div>
    </nav>

    <!-- Main View Content Area -->
    <main class="flex-1 py-6">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-5">
                <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-xl shadow-xs text-xs font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-5">
                <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-xl shadow-xs text-xs">
                    <p class="font-bold mb-1">Please review the following input errors:</p>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Global Accessible Footer -->
    <footer class="bg-white dark:bg-[#111827] text-slate-500 dark:text-slate-400 text-xs py-4 border-t border-slate-200 dark:border-slate-800 text-center transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo-icon.png') }}" alt="Corridex Logo" class="w-5 h-5 rounded">
                <div><strong>Corridex</strong> &bull; Multi-Corridor Freight Visibility Engine (SEA-EU, ASIA-NA, SEA-AU)</div>
            </div>
            <div class="text-[11px] text-slate-400 dark:text-slate-500 font-mono">
                Engine: Laravel 12 &bull; PHP 8.3 &bull; MySQL 8 &bull; Leaflet &bull; Chart.js
            </div>
        </div>
    </footer>
@endif
</body>
</html>
