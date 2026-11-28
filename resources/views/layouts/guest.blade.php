<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Corridex — Multi-Corridor Freight Visibility Engine</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-icon.png') }}">

    <!-- Pre-load blocking theme initialization script to prevent FOUC -->
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'dark';
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
</head>
<body class="bg-slate-50 dark:bg-[#070B12] font-sans text-slate-900 dark:text-slate-100 antialiased min-h-screen selection:bg-blue-600 selection:text-white flex flex-col transition-colors duration-200">
    <!-- Ambient Background Lighting -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-48 -left-48 w-[650px] h-[650px] bg-blue-600/10 rounded-full blur-[150px]"></div>
        <div class="absolute top-1/2 -right-48 w-[550px] h-[550px] bg-sky-500/10 rounded-full blur-[150px]"></div>
        <div class="absolute -bottom-48 left-1/3 w-[550px] h-[550px] bg-indigo-600/10 rounded-full blur-[150px]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b10_1px,transparent_1px),linear-gradient(to_bottom,#1e293b10_1px,transparent_1px)] bg-[size:3.5rem_3.5rem] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_50%,#000_70%,transparent_100%)]"></div>
    </div>

    <!-- Main Viewport Slot -->
    <div class="relative z-10 min-h-screen flex flex-col">
        {{ $slot }}
    </div>
</body>
</html>
