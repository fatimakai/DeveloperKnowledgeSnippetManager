<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Snippets') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('snippets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                    + New Snippet
                </a>
                <a href="{{ route('snippets.export.all.json') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">
                    Export All JSON
                </a>
                <a href="{{ route('snippets.export.all.pdf') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow">
                    Export All PDF
                </a>
            </div>
        </div>
    </x-slot>

    @livewire('my-snippets')
</x-app-layout>
