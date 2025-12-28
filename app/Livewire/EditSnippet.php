<?php

namespace App\Livewire;

use App\Models\Snippet;
use App\Models\Tag;
use Livewire\Component;
use Livewire\Attributes\On;

class EditSnippet extends Component
{
    public $snippet;
    public $title;
    public $description;
    public $code;
    public $language;
    public $tags;
    public $isPublic;
    
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

    public function mount(Snippet $snippet)
    {
        $this->snippet = $snippet;
        $this->title = $snippet->title;
        $this->description = $snippet->description;
        $this->code = $snippet->code;
        $this->language = $snippet->language;
        $this->isPublic = $snippet->is_public;
        $this->tags = $snippet->tags->pluck('name')->implode(', ');
    }

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

        $this->snippet->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'language' => $validated['language'],
            'code' => $validated['code'],
            'is_public' => $validated['isPublic'] ?? false,
        ]);

        // Handle tags
        if (!empty($validated['tags'])) {
            $tagsArray = array_filter(array_map('trim', explode(',', $validated['tags'])));
            $tagIds = collect($tagsArray)->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName])->id;
            });
            $this->snippet->tags()->sync($tagIds);
        } else {
            $this->snippet->tags()->detach();
        }

        session()->flash('success', 'Snippet updated successfully!');
        return redirect()->route('snippets.index');
    }

    public function render()
    {
        return view('livewire.edit-snippet');
    }
}
