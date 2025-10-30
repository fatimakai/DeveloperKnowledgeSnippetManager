<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Snippet') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:p-8">
            <!-- Card -->
<!-- Card (give the card the padding) -->
<div class="bg-white dark:bg-gray-800 shadow rounded-lg w-[90%] sm:w-80 md:w-96 mx-auto px-8 py-12">
    <form method="POST" action="{{ route('snippets.store') }}">
        @csrf

        <!-- centered inner column: controls the horizontal gutters -->
        <div class=" space-y-6 px-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Title
                </label>
                <input type="text" name="title" id="title"
                       class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                              dark:bg-gray-900 dark:text-gray-200 shadow-sm
                              focus:border-blue-500 focus:ring focus:ring-blue-200
                              focus:ring-opacity-50 p-3"
                       placeholder="e.g. Quick sort implementation" required>
            </div>

            <!-- Language -->
            <div>
                <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Language
                </label>
                <input type="text" name="language" id="language"
                       class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                              dark:bg-gray-900 dark:text-gray-200 shadow-sm
                              focus:border-blue-500 focus:ring focus:ring-blue-200
                              focus:ring-opacity-50 p-3"
                       placeholder="e.g. PHP, JavaScript, Python" required>
            </div>

            <!-- Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Code
                </label>
                <textarea name="code" id="code" rows="8"
                          class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                                 dark:bg-gray-900 dark:text-gray-200 shadow-sm
                                 focus:border-blue-500 focus:ring focus:ring-blue-200
                                 focus:ring-opacity-50 p-3 font-mono text-sm"
                          placeholder="// Paste your snippet here" required></textarea>
            </div>

            <!-- Tags -->
<div>
    <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Tags (comma separated)
    </label>
    <input type="text" name="tags" id="tags"
           value="{{ old('tags') }}"
           class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                  dark:bg-gray-900 dark:text-gray-200 shadow-sm
                  focus:border-blue-500 focus:ring focus:ring-blue-200
                  focus:ring-opacity-50 p-3"
           placeholder="e.g. PHP, Laravel, Sorting">
</div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 mt-8">
                <a href="{{ route('snippets.index') }}"
                   class="px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                          text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit"
                        class="px-5 py-2 rounded-md bg-green-600 text-white font-medium
                               hover:bg-green-700 shadow">
                    Save Snippet
                </button>
            </div>
        </div>
    </form>
</div>

        </div>
    </div>
</x-app-layout>
