<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Snippet') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:p-8">
            <!-- Card -->
<!-- Card (give the card the padding) -->
<div class="bg-white dark:bg-gray-800 shadow rounded-lg w-[90%] sm:w-80 md:w-96 mx-auto px-8 py-12">
    <form method="POST" action="{{ route('snippets.store') }}">
        @csrf

        <!-- centered inner column: controls the horizontal gutters -->
        <div class=" space-y-6 px-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Title
                </label>
                <input type="text" name="title" id="title"
                       class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                              dark:bg-gray-900 dark:text-gray-200 shadow-sm
                              focus:border-blue-500 focus:ring focus:ring-blue-200
                              focus:ring-opacity-50 p-3"
                       placeholder="e.g. Quick sort implementation" required>
            </div>

            <!-- Language -->
            <div>
                <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Language
                </label>
                <input type="text" name="language" id="language"
                       class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                              dark:bg-gray-900 dark:text-gray-200 shadow-sm
                              focus:border-blue-500 focus:ring focus:ring-blue-200
                              focus:ring-opacity-50 p-3"
                       placeholder="e.g. PHP, JavaScript, Python" required>
            </div>

            <!-- Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Code
                </label>
                <textarea name="code" id="code" rows="8"
                          class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                                 dark:bg-gray-900 dark:text-gray-200 shadow-sm
                                 focus:border-blue-500 focus:ring focus:ring-blue-200
                                 focus:ring-opacity-50 p-3 font-mono text-sm"
                          placeholder="// Paste your snippet here" required></textarea>
            </div>

            <!-- Tags -->
<div>
    <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Tags (comma separated)
    </label>

<div class="relative">
    <input type="text" id="tags" name="tags" class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                  dark:bg-gray-900 dark:text-gray-200 shadow-sm
                  focus:border-blue-500 focus:ring focus:ring-blue-200
                  focus:ring-opacity-50 p-3"
           placeholder="e.g. PHP, Laravel, Sorting">
</div>


<div id="tag-cloud" class="flex flex-wrap gap-2 mt-2"></div>


</div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 mt-8">
                <a href="{{ route('snippets.index') }}"
                   class="px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                          text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit"
                        class="px-5 py-2 rounded-md bg-green-600 text-white font-medium
                               hover:bg-green-700 shadow">
                    Save Snippet
                </button>
            </div>
        </div>
    </form>
</div>

        </div>
    </div>
</x-app-layout>


<script>
$(document).ready(function() {
    const input = $('#tags');
    const cloud = $('#tag-cloud');

    // Fetch all tags initially
    function loadTags(query = '') {
        $.getJSON('{{ route("tags.autocomplete") }}', { query: query }, function(data) {
            // Filter matching tags to appear on top
            let sorted = data.sort((a, b) => {
                const q = query.toLowerCase();
                const aMatch = a.name.toLowerCase().includes(q);
                const bMatch = b.name.toLowerCase().includes(q);
                return (aMatch === bMatch) ? 0 : aMatch ? -1 : 1;
            });

            cloud.empty();
            sorted.forEach(tag => {
                const tagEl = $(`<span class="cursor-pointer bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1 rounded-full text-sm hover:bg-gray-300 dark:hover:bg-gray-600" data-tag="${tag.name}">${tag.name}</span>`);
                cloud.append(tagEl);
            });
        });
    }

    // Initial load
    loadTags();

    // Filter tags on typing
    input.on('input', function() {
        let val = $(this).val();
        let lastPart = val.split(',').pop().trim();
        loadTags(lastPart);
    });

    // Clicking a tag adds it to input
    $(document).on('click', '#tag-cloud span', function() {
        let tag = $(this).data('tag');
        let current = input.val().split(',').map(t => t.trim()).filter(t => t !== '');
        if (!current.includes(tag)) current.push(tag);
        input.val(current.join(', ') + (current.length ? ', ' : ''));
        input.focus();
    });
});
</script>