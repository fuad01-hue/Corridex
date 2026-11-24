@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Header Banner -->
    <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-xs flex flex-wrap justify-between items-center gap-4 transition-colors">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded border border-purple-200 dark:border-purple-800">
                    Administrator Security Audit
                </span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">System Audit & Compliance Log</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tamper-evident trail of shipment lifecycle events, status changes, and operator mutations.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('audit-logs.index') }}" class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-wrap gap-3 items-end transition-colors">
        <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Action Type</label>
            <input type="text" name="action" value="{{ request('action') }}" placeholder="e.g. APPEND_TRACKING_EVENT" class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
        </div>
        <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Entity Type</label>
            <input type="text" name="entity_type" value="{{ request('entity_type') }}" placeholder="e.g. Shipment" class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none transition-colors">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-lg shadow-xs transition">Filter Logs</button>
            <a href="{{ route('audit-logs.index') }}" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold px-3 py-2 rounded-lg transition-colors">Reset</a>
        </div>
    </form>

    <!-- Audit Logs Table -->
    <div class="bg-white dark:bg-[#151D2A] border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <th class="px-6 py-3.5">Timestamp</th>
                        <th class="px-6 py-3.5">Actor</th>
                        <th class="px-6 py-3.5">Action</th>
                        <th class="px-6 py-3.5">Entity</th>
                        <th class="px-6 py-3.5">Changes (Old &rarr; New)</th>
                        <th class="px-6 py-3.5">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                    @forelse($auditLogs as $log)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-3 text-slate-400 dark:text-slate-500 whitespace-nowrap">
                            {{ $log->created_at?->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-3 font-sans font-semibold text-slate-800 dark:text-slate-200">
                            {{ $log->user->name ?? 'System / Seed' }}
                            <span class="block text-[10px] text-slate-400 dark:text-slate-500 font-mono">({{ $log->user->role ?? 'system' }})</span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-3 font-sans text-slate-700 dark:text-slate-300">
                            {{ $log->entity_type }} #{{ $log->entity_id }}
                        </td>
                        <td class="px-6 py-3 max-w-xs truncate text-[11px] font-mono text-slate-600 dark:text-slate-400">
                            @if($log->new_values)
                                {{ json_encode($log->new_values) }}
                            @else
                                <span class="text-slate-400 italic">None</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-slate-400 dark:text-slate-500 text-[11px]">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400 italic font-sans">No audit logs recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40">
            {{ $auditLogs->links() }}
        </div>
    </div>
</div>
@endsection
