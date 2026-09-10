<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ $prompt->title }}</p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Version {{ $version->version_number }} comparison</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $version->change_summary }} &middot; {{ $version->created_at->format('M j, Y \a\t g:i A') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('prompts.history.index', $prompt) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">All versions</a>
                @if(! $isCurrent && auth()->user()->can('restoreVersion', $prompt))
                    <form method="POST" action="{{ route('prompts.history.restore', [$prompt, $version]) }}" onsubmit="return confirm('Restore version {{ $version->version_number }}? The current prompt will remain in its history.');">
                        @csrf
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Restore this version</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    @php
        $fields = [
            'title' => 'Title',
            'description' => 'Description',
            'target_model' => 'Target model',
            'visibility' => 'Visibility',
            'tags' => 'Tags',
            'prompt_text' => 'Prompt text',
            'example_input' => 'Example input',
            'example_output' => 'Example output',
        ];
        $historical = $version->snapshot();
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        @if($isCurrent)
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
                This snapshot matches the current prompt.
            </div>
        @endif

        @foreach($fields as $field => $label)
            @php($changed = $historical[$field] !== $current[$field])
            <section class="overflow-hidden rounded-xl border {{ $changed ? 'border-amber-300 dark:border-amber-700' : 'border-gray-200 dark:border-gray-700' }} bg-white shadow-sm dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">{{ $label }}</h2>
                    @if($changed)
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950 dark:text-amber-300">Changed</span>
                    @else
                        <span class="text-xs font-medium text-gray-400">Unchanged</span>
                    @endif
                </div>
                <div class="grid md:grid-cols-2">
                    <div class="border-b border-gray-200 p-5 md:border-b-0 md:border-r dark:border-gray-700">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-500">Version {{ $version->version_number }}</p>
                        @if($field === 'tags')
                            <div class="flex flex-wrap gap-2">
                                @forelse($historical[$field] as $tag)
                                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">#{{ $tag }}</span>
                                @empty
                                    <span class="text-sm italic text-gray-400">None</span>
                                @endforelse
                            </div>
                        @else
                            <pre class="whitespace-pre-wrap break-words font-sans text-sm leading-6 text-gray-700 dark:text-gray-200">{{ $historical[$field] ?: 'Not provided' }}</pre>
                        @endif
                    </div>
                    <div class="p-5">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-500">Current</p>
                        @if($field === 'tags')
                            <div class="flex flex-wrap gap-2">
                                @forelse($current[$field] as $tag)
                                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">#{{ $tag }}</span>
                                @empty
                                    <span class="text-sm italic text-gray-400">None</span>
                                @endforelse
                            </div>
                        @else
                            <pre class="whitespace-pre-wrap break-words font-sans text-sm leading-6 text-gray-700 dark:text-gray-200">{{ $current[$field] ?: 'Not provided' }}</pre>
                        @endif
                    </div>
                </div>
            </section>
        @endforeach
    </div>
</x-app-layout>
