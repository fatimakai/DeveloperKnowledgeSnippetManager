<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Your Snippets') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Manage your code snippets</h3>
            <a href="{{ route('snippets.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                + New Snippet
            </a>
        </div>

        <div class="grid gap-6">
            @forelse ($snippets as $snippet)
                <div class="p-6 bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
                    <!-- Title + Language badge -->
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">
                            {{ $snippet->title }}
                        </h3>
                        <span class="px-4 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            {{ strtoupper($snippet->language) }}
                        </span>
                    </div>

                    <!-- Code block -->
                    <div class="mb-4 border border-gray-200 rounded-lg bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                        <pre class="bg-transparent text-gray-800 dark:text-gray-100 rounded-lg p-6 overflow-x-auto text-sm font-mono leading-relaxed">{{ $snippet->code }}</pre>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex space-x-3 mt-3 justify-end items-center">
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
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                    <p>No snippets yet. Create your first one!</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
