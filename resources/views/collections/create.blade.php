<x-app-layout>
    <x-slot name="header"><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create collection</h1></x-slot>
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
        <form method="POST" action="{{ route('collections.store') }}" class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">@csrf
            <div><label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Name</label><input id="name" name="name" value="{{ old('name') }}" maxlength="120" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">@error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div><label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Description <span class="text-gray-400">(optional)</span></label><textarea id="description" name="description" rows="4" maxlength="2000" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('description') }}</textarea>@error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div class="flex justify-end gap-3"><a href="{{ route('collections.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold dark:border-gray-600">Cancel</a><button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Create collection</button></div>
        </form>
    </div>
</x-app-layout>
