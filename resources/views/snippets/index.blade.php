<x-app-layout>
    <x-slot name="header">
            <div class="flex items-center justify-between">

        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('All Snippets') }}
        </h2>
        <a href="{{ route('snippets.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                + New Snippet
        </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Search and Filter Snippets</h3>

        </div>

        <!-- Search and Filters Section -->
        <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
            <form method="GET" action="{{ route('snippets.index') }}" class="space-y-0">
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
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Snippet title..." 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <!-- Language Filter -->
                    <div class="flex-1 min-w-0">
                        <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Language
                        </label>
                        <select 
                            id="language" 
                            name="language" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All</option>
                            @foreach($languages as $lang)
                                <option value="{{ $lang }}" {{ request('language') === $lang ? 'selected' : '' }}>
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
                            name="tag" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All</option>
                            @foreach($tags as $tagItem)
                                <option value="{{ $tagItem->name }}" {{ request('tag') === $tagItem->name ? 'selected' : '' }}>
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
                            name="visibility" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All</option>
                            <option value="public" {{ request('visibility') === 'public' ? 'selected' : '' }}>Public</option>
                            <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>Private</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 flex-shrink-0">
                        <button 
                            type="submit" 
                            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition text-sm whitespace-nowrap"
                        >
                            Search
                        </button>
                        <a 
                            href="{{ route('snippets.index') }}" 
                            class="px-3 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-white rounded-lg font-medium transition text-center text-sm whitespace-nowrap"
                        >
                            Clear
                        </a>
                    </div>
                </div>
            </form>
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
                            <span style="background-color: #e0e7ff; color: #4f46e5; padding: 0.25rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500;" class="dark:bg-indigo-900 dark:text-indigo-300">
                                {{ $snippet->title }}
                            </span>
                            @if($snippet->user_id !== auth()->id() && $snippet->user)
                                <span style="background-color: #dbeafe; color: #1e40af; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;" class="dark:bg-blue-900 dark:text-blue-300">
                                    By {{ $snippet->user->name }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-wrap justify-end">
                            <span class="px-4 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ strtoupper($snippet->language) }}
                            </span>
                            @if($snippet->is_public)
                                <span style="background-color: #dcfce7; color: #166534; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;" class="dark:bg-green-900 dark:text-green-300" title="This snippet is public">
                                    🌍 Public
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300" title="This snippet is private">
                                    🔒 Private
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Tags -->
                    @if($snippet->tags->count() > 0)
                        <div class="mb-3 flex flex-wrap gap-2">
                            @foreach($snippet->tags as $tag)
                                <a 
                                    href="{{ route('snippets.index', ['tag' => $tag->name]) }}"
                                    class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition"
                                >
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
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
                    <div class="flex space-x-3 mt-3 justify-end items-center">
                        @if($snippet->user_id === auth()->id())
                            <a href="{{ route('snippets.edit', $snippet) }}"
                               class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded shadow">
                                ✏️ Edit
                            </a>
                            <form action="{{ route('snippets.destroy', $snippet) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this snippet?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded shadow">
                                    🗑 Delete
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Read-only (not your snippet)
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                    <p>No snippets found. Try adjusting your filters or create your first one!</p>
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
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Highlight all code blocks
        document.querySelectorAll('pre code').forEach(block => {
            hljs.highlightElement(block);
        });
    });
</script>
