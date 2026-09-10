<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <form wire:submit="save" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="grid gap-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Title</label>
                <input id="title" wire:model.blur="title" type="text" maxlength="255" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white" required>
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="target-model" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Target model</label>
                <input id="target-model" wire:model.blur="targetModel" type="text" maxlength="100" list="model-options" placeholder="e.g. GPT-4.1, Claude Sonnet" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white" required>
                <datalist id="model-options">
                    <option value="GPT-4.1"><option value="GPT-4o"><option value="Claude Sonnet"><option value="Gemini 2.5 Pro"><option value="Model agnostic">
                </datalist>
                @error('targetModel') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Visibility</label>
                <select id="visibility" wire:model="visibility" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="private">Private</option>
                    <option value="public">Public</option>
                </select>
                @error('visibility') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Description <span class="text-gray-400">(optional)</span></label>
            <textarea id="description" wire:model.blur="description" rows="3" maxlength="2000" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="prompt-text" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Prompt text</label>
            <textarea id="prompt-text" wire:model.blur="promptText" rows="12" maxlength="50000" class="mt-1 block w-full rounded-lg border-gray-300 font-mono text-sm dark:border-gray-600 dark:bg-gray-950 dark:text-white" required></textarea>
            @error('promptText') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label for="example-input" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Example input <span class="text-gray-400">(optional)</span></label>
                <textarea id="example-input" wire:model.blur="exampleInput" rows="6" maxlength="10000" class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                @error('exampleInput') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="example-output" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Example output <span class="text-gray-400">(optional)</span></label>
                <textarea id="example-output" wire:model.blur="exampleOutput" rows="6" maxlength="10000" class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                @error('exampleOutput') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tags <span class="text-gray-400">(comma separated, maximum 10)</span></label>
            <input id="tags" wire:model.blur="tags" type="text" maxlength="500" placeholder="marketing, support, extraction" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            @error('tags') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
            <div>
                @if(isset($prompt))
                    <button type="button" wire:click="delete" wire:confirm="Delete this prompt permanently?" class="text-sm font-semibold text-red-600 hover:text-red-500">Delete prompt</button>
                @endif
            </div>
            <div class="flex gap-3">
                <a href="{{ route('prompts.mine') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-600 dark:text-gray-200">Cancel</a>
                <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-60">
                    <span wire:loading.remove>{{ $submitLabel }}</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </div>
    </form>
</div>
