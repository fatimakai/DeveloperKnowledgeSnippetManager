<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Your workspace</p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My prompts</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('prompts.export.all') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Export JSON</a>
                <a href="{{ route('prompts.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New prompt</a>
            </div>
        </div>
    </x-slot>

    <livewire:prompt-browser mode="mine" />
</x-app-layout>
