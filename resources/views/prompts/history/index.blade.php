<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ $prompt->title }}</p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Version history</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('prompts.show', $prompt) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Open prompt</a>
                <a href="{{ route('prompts.edit', $prompt) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Edit prompt</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50 p-5 text-sm text-indigo-900 dark:border-indigo-900 dark:bg-indigo-950 dark:text-indigo-100">
            Every meaningful edit creates an immutable snapshot. Restoring an older snapshot creates a new version, so no history is lost.
        </div>

        <div class="space-y-4">
            @forelse($versions as $version)
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="font-bold text-gray-900 dark:text-white">Version {{ $version->version_number }}</h2>
                                @if($version->version_number === $currentVersionNumber)
                                    <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-950 dark:text-green-300">Current</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">{{ $version->change_summary }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $version->created_at->format('M j, Y \a\t g:i A') }}
                                @if($version->creator)
                                    &middot; {{ $version->creator->name }}
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('prompts.history.show', [$prompt, $version]) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:border-indigo-400 hover:text-indigo-600 dark:border-gray-600 dark:text-gray-200">Compare</a>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-500 dark:border-gray-700">
                    No versions have been recorded yet. Saving the prompt will create the first snapshot.
                </div>
            @endforelse
        </div>

        @if($versions->hasPages())
            <div class="mt-8">{{ $versions->links() }}</div>
        @endif
    </div>
</x-app-layout>
