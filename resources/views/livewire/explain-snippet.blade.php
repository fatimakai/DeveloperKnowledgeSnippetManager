<div 
    wire:poll.5s="checkExplanation" 
    class="explain-snippet-container"
    wire:key="explain-snippet-{{ $snippet->id }}-{{ $refreshTrigger }}"
>
    <!-- Error Message -->
    @if($errorMessage)
        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg">
            <p class="text-sm text-red-800 dark:text-red-200">
                <i class="material-icons" style="font-size: 1rem; vertical-align: middle;">error</i>
                {{ $errorMessage }}
            </p>
        </div>
    @endif

    <!-- Loading State -->
    @if($isGenerating)
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg">
            <div class="flex items-center gap-3">
                <div class="animate-spin">
                    <i class="material-icons" style="font-size: 1.5rem;">cached</i>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        Generating explanation...
                    </p>
                    <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">
                        This may take up to 30 seconds. We'll display it below once ready.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Explanation Display -->
    @if($hasExplanation && $explanation && !$isGenerating)
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <i class="material-icons" style="font-size: 1.5rem; color: #10b981;">check_circle</i>
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        Explanation Generated
                    </p>
                </div>
                <button 
                    wire:click="regenerateExplanation"
                    class="px-3 py-1 text-xs font-medium bg-green-600 hover:bg-green-700 text-white rounded transition"
                    title="Generate a new explanation"
                >
                    <i class="material-icons" style="font-size: 0.875rem; vertical-align: middle;">refresh</i>
                    Regenerate
                </button>
            </div>

            <!-- Explanation Content (Markdown) -->
            <div class="prose prose-sm dark:prose-invert max-w-none">
                <div class="space-y-3 text-gray-900 dark:text-gray-100 text-sm leading-relaxed">
                    {!! Str::markdown($explanation->content) !!}
                </div>
            </div>
        </div>
    @elseif(!$isGenerating)
        <!-- Generate Button -->
        @if(auth()->check())
            <button 
                wire:click="generateExplanation"
                class="mt-4 px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white font-medium rounded-lg shadow transition flex items-center gap-2"
                wire:loading.attr="disabled"
            >
                <i class="material-icons" style="font-size: 1.25rem;">lightbulb</i>
                Explain This Code
                @if($remainingExplanations > 0)
                    <span class="text-xs font-normal opacity-85">({{ $remainingExplanations }} left today)</span>
                @endif
            </button>

            @if($remainingExplanations === 0)
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    You've reached your daily limit. Come back tomorrow!
                </p>
            @endif
        @else
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                    Log in
                </a> to generate code explanations.
            </p>
        @endif
    @endif
</div>
