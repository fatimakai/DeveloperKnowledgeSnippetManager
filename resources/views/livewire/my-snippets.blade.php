<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Search and Filter snippets</h3>
    </div>

    <!-- Search and Filters Section -->
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
        <!-- Search and Filter Row -->
        <div class="flex flex-col sm:flex-row gap-3 items-end">
            <!-- Search Input -->
            <div class="flex-1 min-w-0">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Search
                </label>
                <input 
                    type="text" 
                    id="search"
                    wire:model.live="search"
                    placeholder="Snippet title..." 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <!-- Language Filter -->
            <div class="flex-1 min-w-0">
                <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Language
                </label>
                <select 
                    id="language"
                    wire:model.live="language"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">All</option>
                    @foreach($languages as $lang)
                        <option value="{{ $lang }}">
                            {{ strtoupper($lang) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tag Filter -->
            <div class="flex-1 min-w-0">
                <label for="tag" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tags
                </label>
                <select 
                    id="tag"
                    wire:model.live="tagFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">All</option>
                    @foreach($tags as $tagItem)
                        <option value="{{ $tagItem->name }}">
                            {{ $tagItem->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Visibility Filter -->
            <div class="flex-1 min-w-0">
                <label for="visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Visibility
                </label>
                <select 
                    id="visibility"
                    wire:model.live="visibility"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">All</option>
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 flex-shrink-0">
                <button 
                    wire:click="clearFilters"
                    class="px-3 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-white rounded-lg font-medium transition text-center text-sm whitespace-nowrap"
                >
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Results Count -->
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Found <span class="font-semibold">{{ $snippets->count() }}</span> snippet(s)
    </div>

    <div class="grid gap-6">
        @forelse ($snippets as $snippet)
            <div class="p-6 bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <!-- Title + Language badge + Visibility -->
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-white font-semibold text-lg">
                            {{ $snippet->title }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap justify-end">
                        <span class="px-4 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            {{ strtoupper($snippet->language) }}
                        </span>
                        @if($snippet->is_public)
                            <span title="This snippet is public" style="font-size: 1.5rem; color: #6366f1;">
                                <i class="material-icons" style="font-size: 1.5rem; vertical-align: middle; color: #6366f1;">public</i>
                            </span>
                        @else
                            <span title="This snippet is private" style="font-size: 1.5rem; color: #6366f1;">
                                <i class="material-icons" style="font-size: 1.5rem; vertical-align: middle; color: #6366f1;">lock</i>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Tags -->
                @if($snippet->tags->count() > 0)
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach($snippet->tags as $tag)
                            <button 
                                wire:click="$set('tagFilter', '{{ $tag->name }}')"
                                class="px-4 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-white transition"
                            >
                                #{{ $tag->name }}
                            </button>
                        @endforeach
                    </div>
                @endif

                <!-- Description -->
                @if($snippet->description)
                    <div class="mb-4 p-3 rounded-lg text-sm text-white border border-gray-200 dark:border-gray-700" style="background-color: #282c34;">
                        {{ $snippet->description }}
                    </div>
                @endif

                <!-- Code block -->
                <div class="mb-4 border border-gray-200 rounded-lg bg-gray-50 dark:border-gray-700" style="background-color: #282c34;">
                    <pre class="text-gray-800 dark:text-gray-100 rounded-lg p-6 overflow-x-auto text-sm font-mono leading-relaxed max-h-48 m-0" style="max-height: 12rem; overflow-y: auto; background-color: #282c34;"><code class="language-{{ strtolower($snippet->language) }}">{{ $snippet->code }}</code></pre>
                </div>
                <div class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                    {{ count(explode("\n", $snippet->code)) }} lines
                </div>

                <!-- Action buttons -->
                <div class="flex space-x-2 mt-3 justify-end items-center flex-wrap gap-2">
                    <!-- Export buttons (available to all) -->
                    <a href="{{ route('snippets.export.json', $snippet) }}"
                       class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded shadow flex items-center gap-1"
                       title="Download as JSON">
                        <i class="material-icons" style="font-size: 1rem;">code</i> JSON
                    </a>
                    <a href="{{ route('snippets.export.pdf', $snippet) }}"
                       class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded shadow flex items-center gap-1"
                       title="Download as PDF">
                        <i class='bx bxs-file-pdf'></i> PDF
                    </a>

                    <a href="{{ route('snippets.edit', $snippet) }}"
                       class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded shadow flex items-center gap-1">
                        <i class='bx bxs-edit'></i> Edit
                    </a>
                    @livewire('delete-snippet', ['snippet' => $snippet], key('delete-' . $snippet->id))
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                <p>You haven't created any snippets yet or no results match your filters.</p>
                <a href="{{ route('snippets.create') }}" class="text-blue-600 hover:text-blue-700 mt-2 inline-block">
                    Create your first snippet
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($snippets->hasPages())
        <div class="mt-8">
            {{ $snippets->links() }}
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Highlight all code blocks
        document.querySelectorAll('pre code').forEach(block => {
            hljs.highlightElement(block);
        });
    });

    // Re-highlight when Livewire updates
    document.addEventListener('livewire:updated', function() {
        document.querySelectorAll('pre code').forEach(block => {
            hljs.highlightElement(block);
        });
    });
</script>
