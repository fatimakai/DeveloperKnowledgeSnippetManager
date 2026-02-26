<div class="flex-shrink-0">
    <button 
        wire:click="toggleSave"
        title="{{ $isSaved ? 'Remove from saved' : 'Save snippet' }}"
        class="px-3 py-1 rounded text-sm font-medium transition flex items-center gap-1 {{ $isSaved ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}"
    >
        <i class="material-icons" style="font-size: 1rem;">{{ $isSaved ? 'bookmark' : 'bookmark_border' }}</i>
        {{ $isSaved ? 'Saved' : 'Save' }}
    </button>
</div>
