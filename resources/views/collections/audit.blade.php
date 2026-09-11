<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Collection audit log</h1><p class="mt-1 text-sm text-gray-500">{{ $collection->name }}</p></div>
            <a href="{{ route('collections.show', $collection) }}" class="text-sm font-semibold text-indigo-600">Back to collection</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-900"><tr><th class="px-5 py-3">Time</th><th class="px-5 py-3">Actor</th><th class="px-5 py-3">Action</th><th class="px-5 py-3">Context</th><th class="px-5 py-3">IP address</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($logs as $log)
                            <tr><td class="whitespace-nowrap px-5 py-4 text-gray-500">{{ $log->created_at->format('M j, Y H:i') }}</td><td class="px-5 py-4 font-medium dark:text-white">{{ $log->actor?->name ?? 'Deleted user' }}</td><td class="px-5 py-4"><code class="rounded bg-gray-100 px-2 py-1 text-xs dark:bg-gray-900">{{ $log->action }}</code></td><td class="px-5 py-4 text-gray-600 dark:text-gray-300">@if($log->targetUser)Member: {{ $log->targetUser->name }} @endif @if($log->prompt)Prompt: {{ $log->prompt->title }} @endif @if(isset($log->metadata['old_role'], $log->metadata['new_role'])){{ $log->metadata['old_role'] }} &rarr; {{ $log->metadata['new_role'] }}@endif</td><td class="whitespace-nowrap px-5 py-4 text-gray-500">{{ $log->ip_address ?? 'Unavailable' }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-500">No activity has been recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $logs->links() }}</div>
    </div>
</x-app-layout>
