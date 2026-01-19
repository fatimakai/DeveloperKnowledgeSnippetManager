<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('All Snippets') }}
            </h2>
            <a href="{{ route('snippets.create') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg shadow">
                + New Snippet
            </a>
        </div>
    </x-slot>

    @livewire('snippets-index')
</x-app-layout>
