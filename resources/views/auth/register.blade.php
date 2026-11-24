<x-guest-layout>
    <div class="max-w-md mx-auto w-full rounded-2xl overflow-hidden border border-slate-800 shadow-2xl shadow-blue-950/50 bg-slate-900/90 backdrop-blur-xl p-8">
        <div class="text-center mb-6">
            <x-application-logo class="justify-center mb-3" />
            <h2 class="text-xl font-extrabold text-white tracking-tight">Create Consignee Account</h2>
            <p class="text-xs text-slate-400 mt-1">Register for portal access to view real-time cargo status.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4 text-xs">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block font-bold uppercase tracking-wider text-slate-300 mb-1">Company / Full Name</label>
                <input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name"
                       class="w-full px-3 py-2.5 rounded-lg bg-slate-950 border border-slate-700 text-slate-100 placeholder-slate-500 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block font-bold uppercase tracking-wider text-slate-300 mb-1">Work Email</label>
                <input id="email" type="email" name="email" :value="old('email')" required autocomplete="username"
                       class="w-full px-3 py-2.5 rounded-lg bg-slate-950 border border-slate-700 text-slate-100 placeholder-slate-500 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block font-bold uppercase tracking-wider text-slate-300 mb-1">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="w-full px-3 py-2.5 rounded-lg bg-slate-950 border border-slate-700 text-slate-100 placeholder-slate-500 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block font-bold uppercase tracking-wider text-slate-300 mb-1">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full px-3 py-2.5 rounded-lg bg-slate-950 border border-slate-700 text-slate-100 placeholder-slate-500 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>

            <div class="pt-2 flex items-center justify-between">
                <a class="text-blue-400 hover:text-blue-300 text-xs" href="{{ route('login') }}">
                    Already registered?
                </a>

                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 px-5 rounded-lg shadow-lg shadow-blue-500/25 transition text-xs">
                    Create Account &rarr;
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
