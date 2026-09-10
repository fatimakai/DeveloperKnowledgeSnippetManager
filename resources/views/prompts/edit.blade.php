<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit prompt</h1>
    </x-slot>

    <livewire:edit-prompt :prompt="$prompt" />
</x-app-layout>
