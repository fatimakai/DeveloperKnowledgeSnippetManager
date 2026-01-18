<div>
    <!-- Delete Button -->
    <button 
        type="button"
        wire:click="openConfirmation"
        class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded shadow flex items-center gap-1">
        <i class='bx bxs-trash'></i> Delete
    </button>

    <!-- Confirmation Modal -->
    @if($showConfirmation)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click="showConfirmation = false">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-[90%] transform transition-all">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                        Delete Snippet
                    </h3>
                    <button 
                        type="button"
                        wire:click="closeConfirmation"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
                        ✕
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="mb-6">
                    <p class="text-gray-700 dark:text-gray-300 mb-3">
                        Are you sure you want to delete this snippet?
                    </p>
                    <div class="bg-gray-100 dark:bg-gray-700 rounded p-3 mb-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Title:</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ $snippet->title }}
                        </p>
                    </div>
                    <p class="text-sm text-red-600 dark:text-red-400">
                        ⚠️ This action cannot be undone.
                    </p>
                </div>

                <!-- Modal Actions -->
                <div class="flex gap-3 justify-end">
                    <button 
                        type="button"
                        wire:click="closeConfirmation"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-white rounded-lg font-medium transition">
                        Cancel
                    </button>
                    <button 
                        type="button"
                        wire:click="delete"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                        <span wire:loading.remove>Delete Permanently</span>
                        <span wire:loading>Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
