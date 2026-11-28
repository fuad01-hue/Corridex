<x-guest-layout>
    <div class="min-h-screen w-full relative flex flex-col bg-slate-50 dark:bg-[#070B12] text-slate-900 dark:text-slate-100 transition-colors duration-200 overflow-x-hidden">
        <!-- Bright Ocean-Blue Maritime Wave Header Background -->
        <div class="relative bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-700 dark:from-[#081529] dark:via-[#0B2242] dark:to-[#0A1628] text-white pb-28 pt-6 overflow-hidden">
            <!-- Subtle Glows -->
            <div class="absolute -top-24 -left-24 w-80 h-80 bg-cyan-300/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-10 right-0 w-96 h-96 bg-sky-300/15 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Navigation Bar -->
            <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/25 shadow-lg group-hover:scale-105 transition p-2">
                        <img src="{{ asset('images/logo-white.png') }}" alt="CX Logo" class="w-full h-auto object-contain">
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-extrabold tracking-tight text-white leading-none">CORRIDEX</span>
                            <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase tracking-widest rounded-full bg-white/15 text-sky-100 border border-white/25">ENGINE</span>
                        </div>
                        <span class="text-[10px] text-sky-100/90 mt-0.5 block font-medium">Multi-Corridor Freight Visibility</span>
                    </div>
                </a>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleTheme()" aria-label="Toggle visual theme" title="Toggle Light / Dark mode" class="p-2 text-white/90 hover:text-white rounded-lg border border-white/25 bg-white/10 hover:bg-white/20 backdrop-blur-md transition focus:outline-none">
                        <svg class="w-4 h-4 text-amber-300 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                        <svg class="w-4 h-4 text-white block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>

                    <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/95 hover:text-white transition px-3 py-2 rounded-lg border border-white/25 bg-white/10 hover:bg-white/20 backdrop-blur-md">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Homepage</span>
                    </a>
                </div>
            </div>

            <!-- Hero Header + Top-Down Container Ship Inline SVG Illustration -->
            <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-center md:text-left space-y-2 max-w-lg">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold bg-white/15 text-sky-50 border border-white/25 backdrop-blur-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                        <span>Maritime Command &amp; Telemetry</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        Sign In to Corridex Control Center
                    </h1>
                    <p class="text-xs sm:text-sm text-sky-100/90 leading-relaxed">
                        Monitor live container vessels, manage Indonesian &amp; global port corridors, and resolve SLA exceptions.
                    </p>
                </div>

                <!-- Custom Top-Down Container Ship Inline SVG -->
                <div class="shrink-0">
                    <svg class="w-56 h-24 sm:w-64 sm:h-28 drop-shadow-xl" viewBox="0 0 280 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Wake ripples -->
                        <path d="M8 55C18 46 28 46 38 55C28 64 18 64 8 55Z" fill="white" fill-opacity="0.22"/>
                        <path d="M2 55C10 38 22 38 30 55C22 72 10 72 2 55Z" stroke="white" stroke-opacity="0.25" stroke-width="2" stroke-dasharray="4 4"/>
                        <!-- Ship Hull (Top-Down) -->
                        <path d="M36 28H218C242 28 264 42 272 55C264 68 242 82 218 82H36C28 82 24 76 24 68V42C24 34 28 28 36 28Z" fill="#0F172A" stroke="#38BDF8" stroke-width="2.5"/>
                        <!-- Deck -->
                        <path d="M42 33H214C234 33 252 44 259 55C252 66 234 77 214 77H42V33Z" fill="#1E293B"/>
                        <!-- Bridge / Superstructure -->
                        <rect x="48" y="38" width="22" height="34" rx="3" fill="#F8FAFC"/>
                        <rect x="53" y="43" width="12" height="24" rx="1.5" fill="#0284C7"/>
                        <!-- Colorful Container Stacks (Top-Down) -->
                        <rect x="78" y="37" width="26" height="10" rx="1.5" fill="#0EA5E9"/>
                        <rect x="78" y="50" width="26" height="10" rx="1.5" fill="#F59E0B"/>
                        <rect x="78" y="63" width="26" height="10" rx="1.5" fill="#10B981"/>

                        <rect x="108" y="37" width="26" height="10" rx="1.5" fill="#EF4444"/>
                        <rect x="108" y="50" width="26" height="10" rx="1.5" fill="#38BDF8"/>
                        <rect x="108" y="63" width="26" height="10" rx="1.5" fill="#6366F1"/>

                        <rect x="138" y="37" width="26" height="10" rx="1.5" fill="#10B981"/>
                        <rect x="138" y="50" width="26" height="10" rx="1.5" fill="#0EA5E9"/>
                        <rect x="138" y="63" width="26" height="10" rx="1.5" fill="#F59E0B"/>

                        <rect x="168" y="37" width="26" height="10" rx="1.5" fill="#6366F1"/>
                        <rect x="168" y="50" width="26" height="10" rx="1.5" fill="#EF4444"/>
                        <rect x="168" y="63" width="26" height="10" rx="1.5" fill="#38BDF8"/>

                        <rect x="198" y="42" width="22" height="11" rx="1.5" fill="#F59E0B"/>
                        <rect x="198" y="57" width="22" height="11" rx="1.5" fill="#10B981"/>
                    </svg>
                </div>
            </div>

            <!-- Ocean Wave Bottom Divider SVG -->
            <svg class="absolute bottom-0 left-0 w-full h-14 text-slate-50 dark:text-[#070B12] fill-current pointer-events-none" viewBox="0 0 1440 80" preserveAspectRatio="none">
                <path d="M0,32 C240,78 480,0 720,42 C960,84 1200,12 1440,48 L1440,80 L0,80 Z"></path>
            </svg>
        </div>

        <!-- Centered Clean Login Card -->
        <div class="relative z-20 -mt-16 flex-1 flex flex-col items-center px-4 pb-12">
            <div class="w-full max-w-md bg-white dark:bg-[#0B111A] rounded-2xl shadow-2xl shadow-slate-900/10 dark:shadow-black/50 border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
                <div class="mb-5">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Account Sign In</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Choose a demo role or enter your credentials below.</p>
                </div>

                <!-- Quick Demo Fill (Operator / Admin / Customer) -->
                <div class="mb-5 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1">
                            <svg class="w-3 h-3 text-amber-500 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" /></svg>
                            <span>Quick Demo Fill</span>
                        </span>
                        <span class="text-[10px] text-slate-500 font-mono">Password: password</span>
                    </div>

                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" onclick="fillCreds('operator@cargotrace.test', 'password', this)" class="role-btn p-2 rounded-lg bg-white dark:bg-slate-900 hover:bg-sky-50 dark:hover:bg-sky-950/60 border border-slate-200 dark:border-slate-800 hover:border-sky-400 dark:hover:border-sky-500/50 text-left transition ring-1 ring-sky-500/50 focus:outline-none">
                            <span class="block text-[11px] font-bold text-sky-600 dark:text-sky-300">Operator</span>
                            <span class="block text-[9px] text-slate-500 truncate">operator@cargotrace.test</span>
                        </button>
                        <button type="button" onclick="fillCreds('admin@cargotrace.test', 'password', this)" class="role-btn p-2 rounded-lg bg-white dark:bg-slate-900 hover:bg-purple-50 dark:hover:bg-purple-950/60 border border-slate-200 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-500/50 text-left transition focus:outline-none">
                            <span class="block text-[11px] font-bold text-purple-600 dark:text-purple-300">Admin</span>
                            <span class="block text-[9px] text-slate-500 truncate">admin@cargotrace.test</span>
                        </button>
                        <button type="button" onclick="fillCreds('customer@cargotrace.test', 'password', this)" class="role-btn p-2 rounded-lg bg-white dark:bg-slate-900 hover:bg-emerald-50 dark:hover:bg-emerald-950/60 border border-slate-200 dark:border-slate-800 hover:border-emerald-400 dark:hover:border-emerald-500/50 text-left transition focus:outline-none">
                            <span class="block text-[11px] font-bold text-emerald-600 dark:text-emerald-300">Customer</span>
                            <span class="block text-[9px] text-slate-500 truncate">customer@cargotrace.test</span>
                        </button>
                    </div>
                </div>

                @if(session('status'))
                    <div class="mb-4 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 p-3 rounded-lg">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 text-xs font-semibold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 p-3 rounded-lg">
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label for="email" class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Work Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email', 'operator@cargotrace.test') }}" required autofocus autocomplete="username"
                                   class="w-full pl-9 pr-3 py-2.5 rounded-xl bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700/80 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 focus:outline-none transition">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block font-semibold text-slate-700 dark:text-slate-300">
                                Password
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[11px] text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 transition">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" type="password" name="password" value="password" required autocomplete="current-password"
                                   class="w-full pl-9 pr-10 py-2.5 rounded-xl bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700/80 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 focus:outline-none transition">
                            <button type="button" onclick="togglePasswordVisibility()" aria-label="Toggle password view" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition">
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                            <input id="remember_me" type="checkbox" name="remember" checked class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-sky-600 focus:ring-sky-500">
                            <span class="text-xs text-slate-600 dark:text-slate-400">Remember this device</span>
                        </label>
                    </div>

                    <div class="pt-1">
                        <button type="submit" id="submit-btn" class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-bold py-2.5 px-4 rounded-xl shadow-md shadow-sky-600/20 transition-all text-xs focus:ring-2 focus:ring-sky-400 focus:outline-none">
                            <span>Sign In to Platform</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                <div class="mt-5 pt-4 border-t border-slate-200 dark:border-slate-800 text-center">
                    <a href="{{ url('/#public-tracking') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-sky-600 dark:hover:text-sky-400 transition inline-flex items-center gap-1">
                        <span>Need to track a container without signing in?</span>
                        <span class="font-semibold text-sky-600 dark:text-sky-400">Track Shipment &rarr;</span>
                    </a>
                </div>
            </div>

            <p class="mt-6 text-[11px] text-slate-500 dark:text-slate-400 text-center">
                &copy; {{ date('Y') }} Corridex &bull; Multi-Corridor Freight Visibility Engine
            </p>
        </div>
    </div>

    <script>
    function fillCreds(email, password, btn) {
        const emailInput = document.getElementById('email');
        const passInput = document.getElementById('password');

        emailInput.value = email;
        passInput.value = password;

        document.querySelectorAll('.role-btn').forEach(b => {
            b.classList.remove('ring-1', 'ring-sky-500/50', 'ring-blue-500/50', 'ring-purple-500/50', 'ring-emerald-500/50');
        });

        if (btn) {
            btn.classList.add('ring-1', 'ring-sky-500/50');
        }

        emailInput.classList.add('ring-2', 'ring-sky-400');
        passInput.classList.add('ring-2', 'ring-sky-400');

        setTimeout(() => {
            emailInput.classList.remove('ring-2', 'ring-sky-400');
            passInput.classList.remove('ring-2', 'ring-sky-400');
        }, 400);
    }

    function togglePasswordVisibility() {
        const passInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.classList.add('text-sky-500');
        } else {
            passInput.type = 'password';
            eyeIcon.classList.remove('text-sky-500');
        }
    }
    </script>
</x-guest-layout>
