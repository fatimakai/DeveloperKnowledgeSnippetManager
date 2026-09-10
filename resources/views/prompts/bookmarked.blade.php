<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Saved for later</p>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bookmarked prompts</h1>
        </div>
    </x-slot>

    <livewire:prompt-browser mode="bookmarked" />
</x-app-layout>
