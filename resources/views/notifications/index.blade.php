@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-6">
    <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs flex flex-wrap justify-between items-center gap-4 transition-colors">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">In-App Notifications & Alerts</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Real-time status changes, ETA delay notifications, and exception alerts.</p>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold px-3.5 py-2 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors">
                Mark All as Read
            </button>
        </form>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notif)
            <div class="bg-white dark:bg-[#151D2A] border {{ $notif->is_read ? 'border-slate-200 dark:border-slate-800' : 'border-blue-300 dark:border-blue-700/60 bg-blue-50/20 dark:bg-blue-950/20' }} rounded-xl p-5 shadow-xs transition-colors">
                <div class="flex justify-between items-start gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            @php
                                $badgeColor = match($notif->type) {
                                    'DELAY_ALERT' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/60 dark:text-red-300 dark:border-red-800',
                                    'EXCEPTION_ALERT' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                                    default => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
                                };
                            @endphp
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded border {{ $badgeColor }}">
                                {{ $notif->type ?? 'NOTIFICATION' }}
                            </span>
                            @if(! $notif->is_read)
                                <span class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                            @endif
                            <h3 class="text-xs font-bold text-slate-900 dark:text-white">{{ $notif->title }}</h3>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 pt-1">{{ $notif->message }}</p>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block pt-1 font-mono">{{ $notif->created_at->diffForHumans() }} ({{ $notif->created_at->format('Y-m-d H:i') }})</span>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if($notif->shipment_id)
                            <a href="{{ route('shipments.show', $notif->shipment_id) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 px-3 py-1.5 bg-blue-50 dark:bg-blue-950/40 rounded-lg border border-blue-200 dark:border-blue-800 transition">
                                View Shipment
                            </a>
                        @endif
                        @if(! $notif->is_read)
                        <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 px-2.5 py-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                Mark Read
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-8 text-center text-slate-400 italic">
                No notifications found.
            </div>
        @endforelse
    </div>

    <div>
        {{ $notifications->links() }}
    </div>
</div>
@endsection
