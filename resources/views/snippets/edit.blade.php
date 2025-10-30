<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Snippet') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:p-8">
            <!-- Card -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg w-[90%] sm:w-80 md:w-96 mx-auto px-8 py-12">

                <form method="POST" action="{{ route('snippets.update', $snippet) }}">
                    @csrf
                    @method('PUT')

                    <!-- centered inner column -->
                    <div class="space-y-6 px-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Title
                            </label>
                            <input type="text" name="title" id="title"
                                   value="{{ old('title', $snippet->title) }}"
                                   class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                                          dark:bg-gray-900 dark:text-gray-200 shadow-sm
                                          focus:border-blue-500 focus:ring focus:ring-blue-200
                                          focus:ring-opacity-50 p-3"
                                   required>
                        </div>

                        <!-- Language -->
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Language
                            </label>
                            <input type="text" name="language" id="language"
                                   value="{{ old('language', $snippet->language) }}"
                                   class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                                          dark:bg-gray-900 dark:text-gray-200 shadow-sm
                                          focus:border-blue-500 focus:ring focus:ring-blue-200
                                          focus:ring-opacity-50 p-3"
                                   required>
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
                                      required>{{ old('code', $snippet->code) }}</textarea>
                        </div>
                        <!-- Tags -->
<!-- Tags -->
<div>
    <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Tags (comma separated)
    </label>
@php
    $tags = old('tags') ?? $snippet->tags->pluck('name')->implode(', ');
@endphp

<input type="text" name="tags" id="tags"
       value="{{ $tags }}"
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
                                    class="px-5 py-2 rounded-md bg-blue-600 text-white font-medium
                                           hover:bg-blue-700 shadow">
                                Update Snippet
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
