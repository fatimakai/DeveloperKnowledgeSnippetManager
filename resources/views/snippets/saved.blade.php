<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Saved Snippets') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('snippets.export.all.json') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg shadow">
                    Export All JSON
                </a>
                <a href="{{ route('snippets.export.all.pdf') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg shadow">
                    Export All PDF
                </a>
            </div>
        </div>
    </x-slot>

    @livewire('saved-snippets-index')
</x-app-layout>
