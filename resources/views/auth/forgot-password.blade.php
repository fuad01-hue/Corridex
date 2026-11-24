<x-guest-layout>
    <div class="max-w-md mx-auto w-full rounded-2xl overflow-hidden border border-slate-800 shadow-2xl shadow-blue-950/50 bg-slate-900/90 backdrop-blur-xl p-8">
        <div class="text-center mb-6">
            <x-application-logo class="justify-center mb-3" />
            <h2 class="text-xl font-extrabold text-white tracking-tight">Reset Password</h2>
            <p class="text-xs text-slate-400 mt-1">Forgot your password? Enter your email address and we will email you a password reset link.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4 text-xs">
            @csrf

            <div>
                <label for="email" class="block font-bold uppercase tracking-wider text-slate-300 mb-1">Registered Work Email</label>
                <input id="email" type="email" name="email" :value="old('email')" required autofocus
                       class="w-full px-3 py-2.5 rounded-lg bg-slate-950 border border-slate-700 text-slate-100 placeholder-slate-500 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div class="pt-2 flex items-center justify-between">
                <a class="text-blue-400 hover:text-blue-300 text-xs" href="{{ route('login') }}">
                    &larr; Back to Login
                </a>

                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg shadow-blue-500/25 transition text-xs">
                    Send Reset Link
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
