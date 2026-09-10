<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Shared collections</h1><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Collaborate without exposing private prompts publicly.</p></div>
            <a href="{{ route('collections.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New collection</a>
        </div>
    </x-slot>
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        @if(session('success'))<div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-950 dark:text-green-200">{{ session('success') }}</div>@endif
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse($collections as $collection)
                <a href="{{ route('collections.show', $collection) }}" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start justify-between gap-3"><h2 class="font-bold text-gray-900 dark:text-white">{{ $collection->name }}</h2><span class="rounded-full bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">{{ ucfirst($collection->pivot->role) }}</span></div>
                    @if($collection->description)<p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ Str::limit($collection->description, 130) }}</p>@endif
                    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">{{ $collection->prompts_count }} prompts &middot; {{ $collection->members_count }} members &middot; Owner {{ $collection->owner->name }}</p>
                </a>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center md:col-span-2 lg:col-span-3 dark:border-gray-700"><p class="font-semibold text-gray-900 dark:text-white">No shared collections yet</p><p class="mt-1 text-sm text-gray-500">Create one, then invite editors or viewers.</p></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $collections->links() }}</div>
    </div>
</x-app-layout>
