<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Saved Snippets') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid gap-6">
            @forelse($snippets as $snippet)
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
                                <span class="px-4 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-white">
                                    #{{ $tag->name }}
                                </span>
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
                        <!-- Save button -->
                        @if($snippet->is_public)
                            @livewire('save-snippet', ['snippet' => $snippet], key('save-' . $snippet->id))
                        @endif

                        <!-- Like button -->
                        @if($snippet->is_public)
                            @livewire('like-snippet', ['snippet' => $snippet], key('like-' . $snippet->id))
                        @endif

                        <!-- Export buttons -->
                        <a href="{{ route('snippets.export.json', [$snippet]) }}"
                           class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded shadow flex items-center gap-1"
                           title="Download as JSON">
                            <i class="material-icons" style="font-size: 1rem;">code</i> JSON
                        </a>
                        <a href="{{ route('snippets.export.pdf', [$snippet]) }}"
                           class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded shadow flex items-center gap-1"
                           title="Download as PDF">
                            <i class='bx bxs-file-pdf'></i> PDF
                        </a>

                        <!-- View button -->
                        <a href="{{ route('snippets.edit', [$snippet]) }}"
                           class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded shadow flex items-center gap-1">
                            <i class='bx bxs-show'></i> View
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                    <p>No saved snippets yet. Save public snippets to add them here!</p>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight all code blocks
            document.querySelectorAll('pre code').forEach(block => {
                hljs.highlightElement(block);
            });
        });

        // Re-highlight when page loads
        document.querySelectorAll('pre code').forEach(block => {
            hljs.highlightElement(block);
        });
    </script>
</x-app-layout>
