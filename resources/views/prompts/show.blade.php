<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="mb-2 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span>{{ $prompt->target_model }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span>{{ ucfirst($prompt->visibility) }}</span>
                    @if($prompt->collection)<span aria-hidden="true">&middot;</span><a href="{{ route('collections.show', $prompt->collection) }}" class="font-semibold text-indigo-600">{{ $prompt->collection->name }}</a>@endif
                    <span aria-hidden="true">&middot;</span>
                    <span>{{ $prompt->upvotes_count }} upvotes</span>
                    @if($prompt->versions_count > 0)
                        <span aria-hidden="true">&middot;</span>
                        <span>Version {{ $prompt->versions_count }}</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $prompt->title }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">By {{ $prompt->user->name }}</p>
            </div>
            @auth
                @can('viewHistory', $prompt)
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('prompts.history.index', $prompt) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Version history</a>
                        @can('update', $prompt)
                        <a href="{{ route('prompts.edit', $prompt) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Edit</a>
                        @endcan
                    </div>
                @endcan
            @endauth
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if($prompt->description)
            <p class="text-lg text-gray-700 dark:text-gray-200">{{ $prompt->description }}</p>
        @endif

        <section x-data="{ copied: false }" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Prompt</h2>
                <button type="button" @click="navigator.clipboard.writeText($refs.prompt.textContent.trim()); copied = true; setTimeout(() => copied = false, 1500)" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                    <span x-text="copied ? 'Copied' : 'Copy prompt'"></span>
                </button>
            </div>
            <pre x-ref="prompt" class="whitespace-pre-wrap rounded-lg bg-gray-950 p-5 text-sm leading-6 text-gray-100">{{ $prompt->prompt_text }}</pre>
        </section>

        @if($prompt->example_input || $prompt->example_output)
            <div class="grid gap-6 md:grid-cols-2">
                @if($prompt->example_input)
                    <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="mb-3 font-semibold text-gray-900 dark:text-white">Example input</h2>
                        <pre class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-200">{{ $prompt->example_input }}</pre>
                    </section>
                @endif
                @if($prompt->example_output)
                    <section class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="mb-3 font-semibold text-gray-900 dark:text-white">Example output</h2>
                        <pre class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-200">{{ $prompt->example_output }}</pre>
                    </section>
                @endif
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            @foreach($prompt->tags as $tag)
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-sm text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">#{{ $tag->name }}</span>
            @endforeach
        </div>

        <a href="{{ route('prompts.export', $prompt) }}" class="inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-500">Download JSON</a>

        @auth
            @can('analyze', $prompt)
                <livewire:analyze-prompt :prompt="$prompt" />
            @endcan
            @can('report', $prompt)
                <details class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800"><summary class="cursor-pointer text-sm font-semibold text-red-600">Report this prompt</summary><form method="POST" action="{{ route('prompts.reports.store', $prompt) }}" class="mt-4 space-y-3">@csrf<select name="reason" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"><option value="">Choose a reason</option><option value="spam">Spam</option><option value="harmful">Harmful content</option><option value="misleading">Misleading</option><option value="copyright">Copyright</option><option value="other">Other</option></select><textarea name="details" rows="3" maxlength="2000" placeholder="Optional details" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea><button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white">Submit report</button></form></details>
            @endcan
        @endauth
    </div>
</x-app-layout>
