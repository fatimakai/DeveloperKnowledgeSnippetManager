<?php

namespace App\Livewire;

use App\Models\Tag;
use Livewire\Component;

class TagAutocomplete extends Component
{
    public $tags = '';
    public $tagInput = '';
    public $tagSuggestions = [];
    public $showTagSuggestions = false;

    public function updated($property)
    {
        if ($property === 'tagInput') {
            $this->updateTagSuggestions();
        }
    }

    public function updateTagSuggestions()
    {
        if (strlen($this->tagInput) < 2) {
            $this->tagSuggestions = [];
            $this->showTagSuggestions = false;
            return;
        }

        // Get existing tags that match the input
        $this->tagSuggestions = Tag::where('name', 'like', "%{$this->tagInput}%")
            ->select('name')
            ->distinct()
            ->limit(5)
            ->pluck('name')
            ->toArray();

        $this->showTagSuggestions = count($this->tagSuggestions) > 0;
    }

    public function selectTag($tagName)
    {
        $currentTags = array_filter(array_map('trim', explode(',', $this->tags)));
        
        if (!in_array($tagName, $currentTags)) {
            $currentTags[] = $tagName;
        }
        
        $this->tags = implode(', ', $currentTags);
        $this->tagInput = '';
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
        
        // Emit event for parent component
        $this->dispatch('tagsUpdated', tags: $this->tags);
    }

    public function removeTag($index)
    {
        $currentTags = array_filter(array_map('trim', explode(',', $this->tags)));
        unset($currentTags[$index]);
        $this->tags = implode(', ', $currentTags);
        
        // Emit event for parent component
        $this->dispatch('tagsUpdated', tags: $this->tags);
    }

    public function render()
    {
        return view('livewire.tag-autocomplete');
    }
}
