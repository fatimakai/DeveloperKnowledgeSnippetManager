<?php

namespace App\Livewire;

use App\Models\Snippet;
use App\Models\Tag;
use Livewire\Component;
use Livewire\Attributes\On;

class CreateSnippet extends Component
{
    public $title = '';
    public $description = '';
    public $code = '';
    public $language = 'php';
    public $tags = '';
    public $isPublic = false;
    
    // Validation state tracking
    public $validatedFields = [];
    public $fieldTouched = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'language' => 'required|string|max:50',
        'code' => 'required|string',
        'tags' => 'nullable|string',
        'isPublic' => 'nullable|boolean',
    ];

    #[On('tagsUpdated')]
    public function updateTags($tags)
    {
        $this->tags = $tags;
        $this->fieldTouched['tags'] = true;
        $this->validateOnly('tags');
    }

    public function updated($property)
    {
        $this->fieldTouched[$property] = true;
        $this->validateOnly($property);
        
        if (!isset($this->errors[$property])) {
            $this->validatedFields[$property] = true;
        }
    }
    
    public function getFieldStatus($field)
    {
        if (!isset($this->fieldTouched[$field])) {
            return 'untouched';
        }
        
        if (isset($this->errors[$field])) {
            return 'invalid';
        }
        
        return 'valid';
    }
    
    public function getCharacterCount($field)
    {
        $value = $this->{$field} ?? '';
        return strlen($value);
    }
    
    public function getCharacterLimit($field)
    {
        $limits = [
            'title' => 255,
            'description' => 1000,
            'code' => null, // No limit on code
        ];
        
        return $limits[$field] ?? null;
    }
    
    public function getTagCount()
    {
        if (empty($this->tags)) {
            return 0;
        }
        
        $tagsArray = array_filter(array_map('trim', explode(',', $this->tags)));
        return count($tagsArray);
    }

    public function save()
    {
        $validated = $this->validate();

        $snippet = Snippet::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'language' => $validated['language'],
            'code' => $validated['code'],
            'user_id' => auth()->id(),
            'is_public' => $validated['isPublic'] ?? false,
        ]);

        // Handle tags
        if (!empty($validated['tags'])) {
            $tagsArray = array_filter(array_map('trim', explode(',', $validated['tags'])));
            $tagIds = collect($tagsArray)->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName])->id;
            });
            $snippet->tags()->sync($tagIds);
        }

        session()->flash('success', 'Snippet created successfully!');
        return redirect()->route('snippets.index');
    }

    public function render()
    {
        return view('livewire.create-snippet');
    }
}
