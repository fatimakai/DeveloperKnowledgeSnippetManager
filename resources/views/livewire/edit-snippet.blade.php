<div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:p-8">
        <!-- Card -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg w-[95%] mx-auto px-8 py-12">
            <form wire:submit="save" class="space-y-6">
                <!-- Display validation errors -->
                @if (count($errors) > 0)
                    <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Title -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $this->getCharacterCount('title') }}/{{ $this->getCharacterLimit('title') }}
                        </span>
                    </div>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="title"
                            wire:model="title"
                            class="mt-2 block w-full rounded-md border-2 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-3 pr-10 transition
                            @php
                                $status = $this->getFieldStatus('title');
                                echo $status === 'valid' ? 'border-green-500 bg-green-50 dark:bg-gray-900' : ($status === 'invalid' ? 'border-red-500 bg-red-50 dark:bg-gray-900' : 'border-gray-300');
                            @endphp"
                            placeholder="e.g. Quick sort implementation" 
                            required>
                        @php $status = $this->getFieldStatus('title'); @endphp
                        @if($status === 'valid')
                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-green-500">✓</span>
                        @elseif($status === 'invalid')
                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-red-500">✕</span>
                        @endif
                    </div>
                    @if($errors->has('title'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->first('title') }}</p>
                    @endif
                </div>

                <!-- Description -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description (Optional)
                        </label>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $this->getCharacterCount('description') }}/{{ $this->getCharacterLimit('description') }}
                        </span>
                    </div>
                    <div class="relative">
                        <textarea 
                            id="description" 
                            wire:model="description"
                            rows="3"
                            class="mt-2 block w-full rounded-md border-2 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-3 transition
                            @php
                                $status = $this->getFieldStatus('description');
                                echo $status === 'valid' ? 'border-green-500 bg-green-50 dark:bg-gray-900' : ($status === 'invalid' ? 'border-red-500 bg-red-50 dark:bg-gray-900' : 'border-gray-300');
                            @endphp"
                            placeholder="e.g. Efficient sorting algorithm with explanation..."></textarea>
                    </div>
                    @if($errors->has('description'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->first('description') }}</p>
                    @endif
                </div>

                <!-- Language -->
                <div class="relative z-50">
                    <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Language <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select 
                            id="language"
                            wire:model="language"
                            class="mt-2 block w-full rounded-md border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-3 relative z-50"
                            required>
                            <option value="">Select a language...</option>
                            <option value="php">PHP</option>
                            <option value="javascript">JavaScript</option>
                            <option value="python">Python</option>
                            <option value="html">HTML</option>
                            <option value="css">CSS</option>
                            <option value="sql">SQL</option>
                            <option value="xml">XML</option>
                            <option value="markdown">Markdown</option>
                            <option value="json">JSON</option>
                            <option value="java">Java</option>
                            <option value="c">C</option>
                            <option value="cpp">C++</option>
                            <option value="rust">Rust</option>
                            <option value="go">Go</option>
                            <option value="bash">Bash</option>
                            <option value="yaml">YAML</option>
                            <option value="typescript">TypeScript</option>
                            <option value="ruby">Ruby</option>
                            <option value="swift">Swift</option>
                            <option value="kotlin">Kotlin</option>
                            <option value="groovy">Groovy</option>
                            <option value="perl">Perl</option>
                            <option value="r">R</option>
                            <option value="matlab">MATLAB</option>
                            <option value="assembly">Assembly</option>
                            <option value="dockerfile">Dockerfile</option>
                            <option value="terraform">Terraform</option>
                            <option value="nginx">Nginx</option>
                            <option value="apache">Apache</option>
                            <option value="graphql">GraphQL</option>
                        </select>
                    </div>
                    @if($errors->has('language'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->first('language') }}</p>
                    @endif
                </div>

                <!-- Code Editor with CodeMirror -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Code <span class="text-red-500">*</span>
                        </label>
                        @php $codeStatus = $this->getFieldStatus('code'); @endphp
                        @if($codeStatus === 'valid')
                            <span class="text-xs text-green-600 dark:text-green-400">✓ Valid</span>
                        @elseif($codeStatus === 'invalid')
                            <span class="text-xs text-red-600 dark:text-red-400">✕ Required</span>
                        @endif
                    </div>
                    <textarea 
                        id="code"
                        wire:model="code"
                        class="mt-2 block w-full rounded-md border-2 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 shadow-sm p-3 font-mono text-sm transition
                        @php
                            $status = $this->getFieldStatus('code');
                            echo $status === 'valid' ? 'border-green-500 bg-green-50 dark:bg-gray-900' : ($status === 'invalid' ? 'border-red-500 bg-red-50 dark:bg-gray-900' : 'border-gray-300');
                        @endphp"
                        rows="15" 
                        required></textarea>
                    @if($errors->has('code'))
                        <p class="text-red-500 text-sm mt-1">{{ $errors->first('code') }}</p>
                    @endif
                </div>

                <!-- Tags Input - Using TagAutocomplete Component -->
                @livewire('tag-autocomplete', ['tags' => $tags])

                <!-- Visibility Toggle -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="isPublic"
                        wire:model="isPublic"
                        class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 focus:ring focus:ring-indigo-200">
                    <label for="isPublic" class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Make this snippet public
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-4 justify-between pt-6">
                    <div>
                        @livewire('delete-snippet', ['snippet' => $snippet])
                    </div>
                    <div class="flex gap-4">
                        <a href="{{ route('snippets.index') }}"
                           class="px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-white rounded-lg font-medium transition">
                            Cancel
                        </a>
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg font-medium transition"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed">
                            <span wire:loading.remove>Update Snippet</span>
                            <span wire:loading>Updating...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CodeMirror Initialization -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeCodeMirror();
    });

    document.addEventListener('livewire:updated', function() {
        // Re-initialize if needed
    });

    function initializeCodeMirror() {
        const textarea = document.getElementById('code');
        if (textarea && !textarea._codeMirrorInitialized) {
            const editor = CodeMirror.fromTextArea(textarea, {
                lineNumbers: true,
                mode: 'text/x-php',
                theme: 'material-darker',
                indentUnit: 4,
                indentWithTabs: false,
                lineWrapping: true,
                styleActiveLine: true,
                matchBrackets: true,
                autoCloseBrackets: true,
            });

            // Update the hidden textarea when CodeMirror content changes
            editor.on('change', function() {
                textarea.value = editor.getValue();
                @this.set('code', editor.getValue());
            });

            textarea._codeMirrorInitialized = true;
            textarea._codeMirror = editor;

            // Update mode based on language selection
            const languageSelect = document.getElementById('language');
            languageSelect.addEventListener('change', function() {
                updateCodeMirrorMode(editor, this.value);
            });
        }
    }

    function updateCodeMirrorMode(editor, language) {
        const modeMap = {
            'php': 'text/x-php',
            'javascript': 'text/javascript',
            'python': 'text/x-python',
            'html': 'text/html',
            'css': 'text/css',
            'sql': 'text/x-sql',
            'xml': 'text/xml',
            'markdown': 'text/x-markdown',
            'json': 'application/json',
            'java': 'text/x-java',
            'c': 'text/x-csrc',
            'cpp': 'text/x-c++src',
            'rust': 'text/x-rustsrc',
            'go': 'text/x-go',
            'bash': 'text/x-sh',
            'yaml': 'text/x-yaml',
            'typescript': 'text/typescript',
            'ruby': 'text/x-ruby',
            'swift': 'text/x-swift',
            'kotlin': 'text/x-kotlin',
        };

        const mode = modeMap[language] || 'text/plain';
        editor.setOption('mode', mode);
    }
</script>
