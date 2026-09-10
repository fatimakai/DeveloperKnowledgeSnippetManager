<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Community library</p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Discover prompts</h1>
            </div>
            @auth
                <a href="{{ route('prompts.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New prompt</a>
            @endauth
        </div>
    </x-slot>

    <livewire:prompt-browser mode="discover" />
</x-app-layout>
