<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Community pulse</p>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-3 lg:px-8">
        <section class="space-y-4 lg:col-span-2">
            <h2 class="text-lg font-bold">Top prompts</h2>
            @forelse($topPrompts as $index => $prompt)
                <article class="flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">{{ $index + 1 }}</span>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('prompts.show', $prompt) }}" class="font-bold hover:text-indigo-600">{{ $prompt->title }}</a>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $prompt->target_model }} · {{ $prompt->upvotes_count }} upvotes · by {{ $prompt->user->name }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">@foreach($prompt->tags->take(4) as $tag)<span class="text-xs text-indigo-600">#{{ $tag->name }}</span>@endforeach</div>
                    </div>
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500">No public prompts yet.</p>
            @endforelse
        </section>

        <section class="space-y-4">
            <h2 class="text-lg font-bold">Top creators</h2>
            @forelse($topCreators as $index => $creator)
                <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div><span class="mr-2 text-sm font-bold text-indigo-600">#{{ $index + 1 }}</span><span class="font-semibold">{{ $creator->name }}</span></div>
                    <span class="text-sm text-gray-500">{{ $creator->prompts_count }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No creators yet.</p>
            @endforelse
        </section>
    </div>
</x-app-layout>
