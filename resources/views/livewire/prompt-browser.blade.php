<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    @if(session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <section class="mb-8 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800" aria-label="Prompt filters">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="xl:col-span-2">
                <label for="prompt-search" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Search</label>
                <input id="prompt-search" wire:model.live.debounce.350ms="search" type="search" placeholder="Search titles, descriptions, or prompt text" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label for="model-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Target model</label>
                <select id="model-filter" wire:model.live="targetModel" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">All models</option>
                    @foreach($targets as $target)
                        <option value="{{ $target }}">{{ $target }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tag-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tag</label>
                <select id="tag-filter" wire:model.live="tag" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">All tags</option>
                    @foreach($tags as $availableTag)
                        <option value="{{ $availableTag->name }}">{{ $availableTag->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="sort-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Sort</label>
                <select id="sort-filter" wire:model.live="sort" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="popular">Most upvoted</option>
                    <option value="recent">Most recent</option>
                </select>
            </div>
        </div>

        @if($mode === 'mine')
            <div class="mt-4 flex flex-wrap items-end gap-3">
                <div>
                    <label for="visibility-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Visibility</label>
                    <select id="visibility-filter" wire:model.live="visibility" class="mt-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        <option value="">All visibility</option>
                        <option value="private">Private</option>
                        <option value="public">Public</option>
                    </select>
                </div>
                <button type="button" wire:click="clearFilters" class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Clear filters</button>
            </div>
        @else
            <button type="button" wire:click="clearFilters" class="mt-4 rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Clear filters</button>
        @endif
    </section>

    <div class="mb-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
        <span>{{ $prompts->total() }} {{ Str::plural('prompt', $prompts->total()) }}</span>
        <span wire:loading>Updating...</span>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @forelse($prompts as $prompt)
            <article class="flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <a href="{{ route('prompts.show', $prompt) }}" class="text-lg font-bold text-gray-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">{{ $prompt->title }}</a>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $prompt->target_model }} &middot; By {{ $prompt->user->name }}</p>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $prompt->isPublic() ? 'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-200' }}">
                        {{ ucfirst($prompt->visibility) }}
                    </span>
                </div>

                @if($prompt->description)
                    <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $prompt->description }}</p>
                @endif

                <pre class="mt-4 max-h-48 overflow-auto whitespace-pre-wrap rounded-lg bg-gray-950 p-4 text-sm leading-6 text-gray-100">{{ Str::limit($prompt->prompt_text, 700) }}</pre>

                @if($prompt->tags->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($prompt->tags as $promptTag)
                            <button type="button" wire:click="$set('tag', @js($promptTag->name))" class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">#{{ $promptTag->name }}</button>
                        @endforeach
                    </div>
                @endif

                <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-6">
                    <div class="flex items-center gap-2">
                        @if($prompt->isPublic())
                            <button type="button" wire:click="toggleUpvote({{ $prompt->id }})" class="rounded-lg border px-3 py-1.5 text-sm font-semibold {{ ($prompt->is_upvoted ?? false) ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'border-gray-300 text-gray-600 dark:border-gray-600 dark:text-gray-300' }}">
                                &uarr; {{ $prompt->upvotes_count }}
                            </button>
                            <button type="button" wire:click="toggleBookmark({{ $prompt->id }})" class="rounded-lg border px-3 py-1.5 text-sm font-semibold {{ ($prompt->is_bookmarked ?? false) ? 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'border-gray-300 text-gray-600 dark:border-gray-600 dark:text-gray-300' }}">
                                {{ ($prompt->is_bookmarked ?? false) ? 'Bookmarked' : 'Bookmark' }}
                            </button>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 text-sm font-semibold">
                        <a href="{{ route('prompts.show', $prompt) }}" class="text-indigo-600 hover:text-indigo-500">Open</a>
                        @auth
                            @if(auth()->id() === $prompt->user_id)
                                <a href="{{ route('prompts.edit', $prompt) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Edit</a>
                                <button type="button" wire:click="deletePrompt({{ $prompt->id }})" wire:confirm="Delete this prompt permanently?" class="text-red-600 hover:text-red-500">Delete</button>
                            @endif
                        @endauth
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 p-12 text-center lg:col-span-2 dark:border-gray-700">
                <h2 class="font-semibold text-gray-900 dark:text-white">No prompts found</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try different filters or add the first prompt.</p>
            </div>
        @endforelse
    </div>

    @if($prompts->hasPages())
        <div class="mt-8">{{ $prompts->links() }}</div>
    @endif
</div>
