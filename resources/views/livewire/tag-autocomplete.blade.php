<div class="space-y-3">
    <!-- Tags Input -->
    <div>
        <div class="flex items-center justify-between mb-2">
            <label for="tag-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tags (Optional)
            </label>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ count(array_filter(array_map('trim', explode(',', $tags ?? '')))) }} selected
            </span>
        </div>
        <div class="mt-2 relative">
            <input 
                type="text" 
                id="tag-input"
                wire:model.live="tagInput"
                class="w-full px-3 py-2 border-2 border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Type to search tags...">
            
            <!-- Autocomplete Dropdown -->
            @if($showTagSuggestions && count($tagSuggestions) > 0)
                <div class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg z-50 max-h-48 overflow-y-auto">
                    @foreach($tagSuggestions as $suggestion)
                        <div 
                            wire:click="selectTag('{{ $suggestion }}')"
                            class="px-3 py-2 hover:bg-blue-50 dark:hover:bg-blue-900 cursor-pointer text-gray-700 dark:text-gray-300 flex items-center gap-2 transition">
                            <span class="text-blue-500">#</span>
                            <span>{{ $suggestion }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Selected Tags Display -->
    @if(!empty($tags))
        <div class="flex flex-wrap gap-2 mt-3">
            @foreach(array_filter(array_map('trim', explode(',', $tags))) as $index => $tag)
                <div class="inline-flex items-center gap-2 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm hover:shadow-md transition">
                    <span>#{{ $tag }}</span>
                    <button 
                        type="button"
                        wire:click="removeTag({{ $index }})"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-bold hover:scale-110 transition">
                        ×
                    </button>
                </div>
            @endforeach
        </div>
    @endif
</div>
