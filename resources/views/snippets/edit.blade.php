<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Snippet') }}
            </h2>
        </div>
    </x-slot>

    @livewire('edit-snippet', ['snippet' => $snippet])
</x-app-layout>
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
                            li.className = 'px-4 py-2 hover:bg-indigo-100 dark:hover:bg-indigo-900 cursor-pointer text-gray-700 dark:text-gray-300';
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
                            li.className = 'px-4 py-2 hover:bg-indigo-100 dark:hover:bg-indigo-900 cursor-pointer text-gray-700 dark:text-gray-300';
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
    
    // Set initial mode if language is already filled
    updateEditorMode();

    // Tag autocomplete
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
