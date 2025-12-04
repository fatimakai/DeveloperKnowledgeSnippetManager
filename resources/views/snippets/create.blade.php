<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Snippet') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:p-8">
            <!-- Card -->
<!-- Card (give the card the padding) -->
<div class="bg-white dark:bg-gray-800 shadow rounded-lg w-[95%] mx-auto px-8 py-12">
    <form method="POST" action="{{ route('snippets.store') }}">
        @csrf

        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Description (Optional)
                </label>
                <textarea name="description" id="description" rows="3"
                       class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                              dark:bg-gray-900 dark:text-gray-200 shadow-sm
                              focus:border-blue-500 focus:ring focus:ring-blue-200
                              focus:ring-opacity-50 p-3"
                       placeholder="e.g. Efficient sorting algorithm with explanation..."></textarea>
            </div>

            <!-- Language -->
            <div class="relative z-50">
                <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Language
                </label>
                <div class="relative">
                    <input type="text" name="language" id="language"
                           class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700
                                  dark:bg-gray-900 dark:text-gray-200 shadow-sm
                                  focus:border-blue-500 focus:ring focus:ring-blue-200
                                  focus:ring-opacity-50 p-3"
                           placeholder="Search or select a language" 
                           autocomplete="off"
                           required>
                    <ul id="language-dropdown" class="absolute top-full left-0 right-0 z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg max-h-48 overflow-y-scroll hidden">
                    </ul>
                </div>
            </div>

            <!-- Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Code
                </label>
                <div id="code-editor" class="mt-2 rounded-md border border-gray-300 dark:border-gray-700 overflow-hidden" style="min-height: 300px;"></div>
                <textarea name="code" id="code" style="display: none;" required></textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Syntax highlighting will update based on the selected language
                </p>
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

            <!-- Visibility Toggle -->
            <div>
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="is_public" id="is_public" value="1" checked
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Make this snippet public (visible to all users)
                    </span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-8">
                    Uncheck to make this snippet private (only visible to you)
                </p>
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

<!-- CodeMirror for Code Editing with Syntax Highlighting -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/python/python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/markdown/markdown.min.js"></script>

<style>
    .CodeMirror {
        font-family: 'Fira Code', monospace !important;
        font-size: 14px !important;
        background-color: #282c34 !important;
        color: #abb2bf !important;
        border: 1px solid #3e4452 !important;
    }
    
    .CodeMirror-gutters {
        background-color: #282c34 !important;
        border-right: 1px solid #3e4452 !important;
    }
    
    .CodeMirror-linenumber {
        color: #5c6370 !important;
    }
    
    .CodeMirror-cursor {
        border-left: 1px solid #abb2bf !important;
    }

    /* Syntax highlighting colors matching Atom One Dark */
    .cm-atom { color: #61afef !important; }
    .cm-number { color: #d19a66 !important; }
    .cm-property { color: #61afef !important; }
    .cm-qualifier { color: #61afef !important; }
    .cm-variable { color: #e06c75 !important; }
    .cm-keyword { color: #c678dd !important; }
    .cm-builtin { color: #61afef !important; }
    .cm-string { color: #98c379 !important; }
    .cm-string-2 { color: #98c379 !important; }
    .cm-comment { color: #5c6370 !important; }
    .cm-tag { color: #e06c75 !important; }
    .cm-attribute { color: #d19a66 !important; }

    /* Ensure dropdown is visible and scrollable */

    #language-dropdown {
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        right: 0 !important;
        max-height: 12rem !important;
        overflow-y: scroll !important;
    }
</style>

<script>
$(document).ready(function() {
    // Language dropdown list
    const languages = ['PHP', 'JavaScript', 'Python', 'HTML', 'CSS', 'SQL', 'XML', 'Markdown', 'JSON', 'Java', 'C', 'C++', 'Rust', 'Go', 'Bash', 'YAML', 'TypeScript', 'Ruby', 'Swift', 'Kotlin', 'Groovy', 'Perl', 'R', 'MATLAB', 'Assembly', 'Dockerfile', 'Terraform', 'Nginx', 'Apache', 'GraphQL'];
    
    const languageInput = document.getElementById('language');
    const languageDropdown = document.getElementById('language-dropdown');
    
    // Show filtered dropdown on input
    languageInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const filtered = languages.filter(lang => lang.toLowerCase().includes(query));
        
        languageDropdown.innerHTML = '';
        if (query && filtered.length > 0) {
            filtered.forEach(lang => {
                const li = document.createElement('li');
                li.className = 'px-4 py-2 hover:bg-blue-100 dark:hover:bg-blue-900 cursor-pointer text-gray-700 dark:text-gray-300';
                li.textContent = lang;
                li.addEventListener('click', function() {
                    languageInput.value = lang;
                    languageDropdown.classList.add('hidden');
                    updateEditorMode();
                });
                languageDropdown.appendChild(li);
            });
            languageDropdown.classList.remove('hidden');
        } else {
            languageDropdown.classList.add('hidden');
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!languageInput.contains(e.target) && !languageDropdown.contains(e.target)) {
            languageDropdown.classList.add('hidden');
        }
    });
    
    // Show dropdown on focus
    languageInput.addEventListener('focus', function() {
        if (this.value === '') {
            languageDropdown.innerHTML = '';
            languages.forEach(lang => {
                const li = document.createElement('li');
                li.className = 'px-4 py-2 hover:bg-blue-100 dark:hover:bg-blue-900 cursor-pointer text-gray-700 dark:text-gray-300';
                li.textContent = lang;
                li.addEventListener('click', function() {
                    languageInput.value = lang;
                    languageDropdown.classList.add('hidden');
                    updateEditorMode();
                });
                languageDropdown.appendChild(li);
            });
            languageDropdown.classList.remove('hidden');
        }
    });

    // Initialize CodeMirror
    const codeTextarea = document.getElementById('code');
    const editor = CodeMirror(document.getElementById('code-editor'), {
        value: codeTextarea.value,
        mode: 'php',  // Default language
        theme: 'default',
        indentUnit: 4,
        indentWithTabs: false,
        lineNumbers: true,
        lineWrapping: true,
        matchBrackets: true,
        autoCloseBrackets: true,
        highlightSelectionMatches: { showToken: /\w/, annotateScrollbar: true },
        styleActiveLine: true,
        foldGutter: true,
        gutters: ['CodeMirror-linenumbers', 'CodeMirror-foldgutter'],
        height: 'auto',
        minHeight: 300
    });

    // Update textarea when editor changes
    editor.on('change', function() {
        codeTextarea.value = editor.getValue();
    });

    // Update CodeMirror mode when language changes
    const updateEditorMode = function() {
        const lang = languageInput.value.toLowerCase().trim();
        const modeMap = {
            'php': 'php',
            'js': 'javascript',
            'javascript': 'javascript',
            'py': 'python',
            'python': 'python',
            'html': 'htmlmixed',
            'css': 'css',
            'sql': 'sql',
            'xml': 'xml',
            'markdown': 'markdown',
            'md': 'markdown',
            'java': 'text',
            'c': 'text',
            'c++': 'text',
            'rust': 'text',
            'go': 'text',
            'bash': 'text',
            'shell': 'text',
            'json': 'javascript',
            'yaml': 'text',
            'typescript': 'javascript',
            'ts': 'javascript',
            'ruby': 'text',
            'swift': 'text',
            'kotlin': 'text',
            'groovy': 'text',
            'perl': 'text',
            'r': 'text',
            'matlab': 'text',
            'assembly': 'text',
            'dockerfile': 'text',
            'terraform': 'text',
            'nginx': 'text',
            'apache': 'text',
            'graphql': 'text',
        };
        
        const mode = modeMap[lang] || 'null';
        editor.setOption('mode', mode);
    };

    languageInput.addEventListener('change', updateEditorMode);

});
</script>