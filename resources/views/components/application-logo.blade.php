<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5 font-sans']) }}>
    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 via-blue-600 to-sky-400 text-white shadow-lg shadow-blue-500/25 ring-1 ring-white/20 p-2">
        <img src="{{ asset('images/logo-white.png') }}" alt="CX" class="w-full h-auto object-contain">
        <span class="absolute -top-1 -right-1 flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
        </span>
    </div>
    <div class="flex flex-col">
        <div class="flex items-center gap-1.5">
            <span class="text-xl font-black tracking-tight text-slate-900 dark:text-white leading-none">
                CORRIDEX
            </span>
            <span class="px-1.5 py-0.5 text-[8px] font-mono font-bold uppercase tracking-widest rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                ENGINE
            </span>
        </div>
        <span class="text-[9px] font-medium tracking-wide text-slate-400 mt-0.5">Multi-Corridor Freight Visibility</span>
    </div>
</div>
