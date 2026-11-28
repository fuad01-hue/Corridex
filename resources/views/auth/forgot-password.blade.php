<x-guest-layout>
    <div class="max-w-md mx-auto w-full my-auto rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-xl dark:shadow-2xl dark:shadow-blue-950/50 bg-white dark:bg-[#0B111A]/90 backdrop-blur-xl p-8 transition-colors">
        <div class="flex justify-end mb-2">
            <button type="button" onclick="toggleTheme()" aria-label="Toggle visual theme" title="Toggle Light / Dark mode" class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-4 h-4 text-amber-400 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                </svg>
                <svg class="w-4 h-4 text-slate-600 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
            </button>
        </div>

        <div class="text-center mb-6">
            <x-application-logo class="justify-center mb-3" />
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Reset Password</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Forgot your password? Enter your email address and we will email you a password reset link.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4 text-xs">
            @csrf

            <div>
                <label for="email" class="block font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Registered Work Email</label>
                <input id="email" type="email" name="email" :value="old('email')" required autofocus
                       class="w-full px-3 py-2.5 rounded-lg bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div class="pt-2 flex items-center justify-between">
                <a class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-xs" href="{{ route('login') }}">
                    &larr; Back to Login
                </a>

                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg shadow-blue-500/25 transition text-xs">
                    Send Reset Link
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
