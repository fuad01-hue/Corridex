<x-guest-layout>
    <div class="min-h-screen w-full grid grid-cols-1 lg:grid-cols-12 relative z-10 bg-slate-950 text-slate-100">
        <!-- Left Column: Global Logistics Network Showcase (7 Cols) -->
        <div class="lg:col-span-7 p-8 sm:p-12 lg:p-16 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-slate-800/80 bg-gradient-to-br from-slate-950 via-[#0B1120] to-[#0A1628] relative overflow-hidden">
            <!-- Subtle Ambient Background Glow -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 right-0 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-8 max-w-2xl">
                <!-- Top Navigation & Brand Header -->
                <div class="flex items-center justify-between">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 ring-1 ring-white/10 group-hover:scale-105 transition p-2">
                            <img src="{{ asset('images/logo-white.png') }}" alt="CX Logo" class="w-full h-auto object-contain">
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xl font-extrabold tracking-tight text-white leading-none">
                                    CORRIDEX
                                </span>
                                <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase tracking-widest rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    ENGINE
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-0.5 block font-medium">Multi-Corridor Freight Visibility Engine</span>
                        </div>
                    </a>

                    <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white transition px-3 py-1.5 rounded-lg border border-slate-800 bg-slate-900/60 hover:bg-slate-800">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Homepage</span>
                    </a>
                </div>

                <!-- Main Value Proposition Headline -->
                <div class="space-y-3 pt-4">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-950/80 text-blue-300 border border-blue-800/60">
                        <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                        <span>Enterprise Telemetry &amp; SLA Management</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        Real-Time Visibility Across Global Trade Corridors
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-xl">
                        Monitor container vessel movements, verify contracted SLA thresholds, and inspect immutable waypoint milestones across primary international shipping lanes.
                    </p>
                </div>

                <!-- Active Strategic Corridors Live Telemetry -->
                <div class="space-y-2.5 pt-2">
                    <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                        <span>Active Trade Corridors</span>
                        <span class="text-emerald-400 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            Live Telemetry Feed
                        </span>
                    </div>

                    <!-- Corridor SEA -> EU -->
                    <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 transition flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div class="truncate">
                                <div class="text-xs font-bold text-slate-200">Southeast Asia &rarr; Northern Europe</div>
                                <div class="text-[11px] text-slate-400 truncate">Singapore / Jakarta &rarr; Rotterdam / Hamburg &bull; Ocean Container</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono font-bold px-2.5 py-1 rounded-md bg-emerald-950/60 text-emerald-300 border border-emerald-800 shrink-0">
                            12h SLA Target
                        </span>
                    </div>

                    <!-- Corridor ASIA -> NA -->
                    <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 transition flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                            </div>
                            <div class="truncate">
                                <div class="text-xs font-bold text-slate-200">East Asia &rarr; North America</div>
                                <div class="text-[11px] text-slate-400 truncate">Shanghai / Busan &rarr; Los Angeles / Long Beach &bull; Transpacific</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono font-bold px-2.5 py-1 rounded-md bg-sky-950/60 text-sky-300 border border-sky-800 shrink-0">
                            18h SLA Target
                        </span>
                    </div>

                    <!-- Corridor SEA -> AU -->
                    <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 transition flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="truncate">
                                <div class="text-xs font-bold text-slate-200">Southeast Asia &rarr; Australia</div>
                                <div class="text-[11px] text-slate-400 truncate">Singapore / Jakarta &rarr; Sydney / Melbourne &bull; Cold Chain &amp; Dry</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono font-bold px-2.5 py-1 rounded-md bg-indigo-950/60 text-indigo-300 border border-indigo-800 shrink-0">
                            24h SLA Target
                        </span>
                    </div>
                </div>

                <!-- Global Trust & Security Badges -->
                <div class="pt-4 flex flex-wrap items-center gap-3 text-xs text-slate-400">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-800">
                        <svg class="w-3.5 h-3.5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        <span>Append-Only Timelines</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-800">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        <span>Deterministic SLA Engine</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-800">
                        <svg class="w-3.5 h-3.5 text-purple-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
                        <span>Role-Isolated Tenants</span>
                    </span>
                </div>
            </div>

            <!-- Bottom Operations Footer -->
            <div class="pt-8 border-t border-slate-800/80 mt-8 relative z-10 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-medium text-slate-400">Global Operations Network: Operational</span>
                </div>
                <div>
                    <span>System Architecture: MySQL 8 &bull; Laravel 12</span>
                </div>
            </div>
        </div>

        <!-- Right Column: Clean Enterprise Authentication Console (5 Cols) -->
        <div class="lg:col-span-5 p-8 sm:p-12 lg:p-14 flex flex-col justify-between bg-slate-900/90 relative backdrop-blur-xl">
            <div>
                <!-- Form Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-black text-white tracking-tight">Sign In to Corridex</h2>
                    <p class="text-xs text-slate-400 mt-1">Access the real-time logistics command center and multi-corridor directory.</p>
                </div>

                <!-- Evaluator Demo Selector (Clean Segmented Tabs) -->
                <div class="mb-6 p-3.5 rounded-xl bg-slate-950 border border-slate-800">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1">
                            <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" /></svg>
                            <span>Quick Demo Fill</span>
                        </span>
                        <span class="text-[10px] text-slate-500 font-mono">Password: password</span>
                    </div>

                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" onclick="fillCreds('operator@cargotrace.test', 'password', this)" class="role-btn p-2 rounded-lg bg-slate-900 hover:bg-blue-950/60 border border-slate-800 hover:border-blue-500/50 text-left transition ring-1 ring-blue-500/50 focus:outline-none">
                            <span class="block text-[11px] font-bold text-blue-300">Operator</span>
                            <span class="block text-[9px] text-slate-500 truncate">operator@cargotrace.test</span>
                        </button>
                        <button type="button" onclick="fillCreds('admin@cargotrace.test', 'password', this)" class="role-btn p-2 rounded-lg bg-slate-900 hover:bg-purple-950/60 border border-slate-800 hover:border-purple-500/50 text-left transition focus:outline-none">
                            <span class="block text-[11px] font-bold text-purple-300">Admin</span>
                            <span class="block text-[9px] text-slate-500 truncate">admin@cargotrace.test</span>
                        </button>
                        <button type="button" onclick="fillCreds('customer@cargotrace.test', 'password', this)" class="role-btn p-2 rounded-lg bg-slate-900 hover:bg-emerald-950/60 border border-slate-800 hover:border-emerald-500/50 text-left transition focus:outline-none">
                            <span class="block text-[11px] font-bold text-emerald-300">Customer</span>
                            <span class="block text-[9px] text-slate-500 truncate">customer@cargotrace.test</span>
                        </button>
                    </div>
                </div>

                <!-- Session Status / Errors -->
                @if(session('status'))
                    <div class="mb-4 text-xs font-semibold text-emerald-400 bg-emerald-950/50 border border-emerald-800 p-3 rounded-lg">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 text-xs font-semibold text-red-400 bg-red-950/50 border border-red-800 p-3 rounded-lg">
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Authentication Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block font-semibold text-slate-300 mb-1.5">
                            Work Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email', 'operator@cargotrace.test') }}" required autofocus autocomplete="username"
                                   class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-slate-950 border border-slate-700/80 text-slate-100 placeholder-slate-500 text-xs focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 focus:outline-none transition">
                        </div>
                    </div>

                    <!-- Password Input with Show/Hide Toggle -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block font-semibold text-slate-300">
                                Password
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[11px] text-blue-400 hover:text-blue-300 transition">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" type="password" name="password" value="password" required autocomplete="current-password"
                                   class="w-full pl-9 pr-10 py-2.5 rounded-lg bg-slate-950 border border-slate-700/80 text-slate-100 placeholder-slate-500 text-xs focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 focus:outline-none transition">
                            <button type="button" onclick="togglePasswordVisibility()" aria-label="Toggle password view" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 transition">
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Option -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" checked class="rounded border-slate-700 bg-slate-950 text-blue-600 focus:ring-blue-500">
                            <span class="text-xs text-slate-400">Remember this device</span>
                        </label>
                    </div>

                    <!-- Submit CTA Button -->
                    <div class="pt-2">
                        <button type="submit" id="submit-btn" class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 px-4 rounded-lg shadow-md shadow-blue-600/20 transition-all text-xs focus:ring-2 focus:ring-blue-400 focus:outline-none">
                            <span>Sign In to Platform</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Public Tracking Quick Link -->
                <div class="mt-5 text-center">
                    <a href="{{ url('/#public-tracking') }}" class="text-xs text-slate-400 hover:text-blue-400 transition inline-flex items-center gap-1">
                        <span>Need to track a container without signing in?</span>
                        <span class="font-semibold text-blue-400">Track Shipment &rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Security & Compliance Footer -->
            <div class="pt-6 border-t border-slate-800/80 mt-8 text-center space-y-1">
                <div class="flex items-center justify-center gap-1.5 text-[11px] font-semibold text-slate-400">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>Enterprise-Grade Security &amp; Data Protection</span>
                </div>
                <p class="text-[10px] text-slate-500">
                    &copy; {{ date('Y') }} Corridex &bull; Multi-Corridor Freight Visibility Engine &bull; All Rights Reserved
                </p>
            </div>
        </div>
    </div>

    <!-- Autofill & Password Visibility JavaScript -->
    <script>
    function fillCreds(email, password, btn) {
        const emailInput = document.getElementById('email');
        const passInput = document.getElementById('password');

        emailInput.value = email;
        passInput.value = password;

        document.querySelectorAll('.role-btn').forEach(b => {
            b.classList.remove('ring-1', 'ring-blue-500/50', 'ring-purple-500/50', 'ring-emerald-500/50');
        });

        if (btn) {
            btn.classList.add('ring-1', 'ring-blue-500/50');
        }

        emailInput.classList.add('ring-2', 'ring-blue-400');
        passInput.classList.add('ring-2', 'ring-blue-400');

        setTimeout(() => {
            emailInput.classList.remove('ring-2', 'ring-blue-400');
            passInput.classList.remove('ring-2', 'ring-blue-400');
        }, 400);
    }

    function togglePasswordVisibility() {
        const passInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.classList.add('text-blue-400');
        } else {
            passInput.type = 'password';
            eyeIcon.classList.remove('text-blue-400');
        }
    }
    </script>
</x-guest-layout>
