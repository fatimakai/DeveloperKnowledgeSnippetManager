<?php

namespace App\Livewire;

use App\Models\SavedSnippet;
use App\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;

class SavedSnippetsIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $language = '';
    public $tagFilter = '';
    
    protected $listeners = ['snippetDeleted' => 'handleSnippetDeleted', 'snippetSaved' => 'handleSnippetSaved'];

    public function updated($property)
    {
        // Reset to first page when any filter changes
        if (in_array($property, ['search', 'language', 'tagFilter'])) {
            $this->resetPage();
        }
    }

    public function getSavedSnippetsProperty()
    {
        $query = SavedSnippet::where('user_id', auth()->id())
            ->with('snippet.user', 'snippet.tags');

        // Search by title
        if (!empty($this->search)) {
            $query->whereHas('snippet', function ($q) {
                $q->where('title', 'like', "%{$this->search}%");
            });
        }

        // Filter by language
        if (!empty($this->language)) {
            $query->whereHas('snippet', function ($q) {
                $q->where('language', $this->language);
            });
        }

        // Filter by tag
        if (!empty($this->tagFilter)) {
            $query->whereHas('snippet.tags', function ($q) {
                $q->where('name', $this->tagFilter);
            });
        }

        return $query->latest()->paginate(15);
    }

    public function getLanguagesProperty()
    {
        return SavedSnippet::where('user_id', auth()->id())
            ->with('snippet')
            ->get()
            ->map(function ($item) {
                return $item->snippet->language;
            })
            ->unique()
            ->sort()
            ->values();
    }

    public function getTagsProperty()
    {
        return Tag::whereHas('snippets.savedSnippets', function ($q) {
            $q->where('user_id', auth()->id());
        })
        ->select('id', 'name')
        ->orderBy('name')
        ->get();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->language = '';
        $this->tagFilter = '';
    }

    public function render()
    {
        return view('livewire.saved-snippets-index', [
            'savedSnippets' => $this->getSavedSnippetsProperty(),
            'languages' => $this->getLanguagesProperty(),
            'tags' => $this->getTagsProperty(),
        ]);
    }
}
