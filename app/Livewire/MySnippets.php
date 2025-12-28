<?php

namespace App\Livewire;

use App\Models\Snippet;
use App\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;

class MySnippets extends Component
{
    use WithPagination;

    public $search = '';
    public $language = '';
    public $tagFilter = '';
    public $visibility = '';

    public function updated($property)
    {
        // Reset to first page when any filter changes
        if (in_array($property, ['search', 'language', 'tagFilter', 'visibility'])) {
            $this->resetPage();
        }
    }

    public function getSnippetsProperty()
    {
        // Only user's own snippets
        $query = Snippet::where('user_id', auth()->id());

        // Search by title
        if (!empty($this->search)) {
            $query->where('title', 'like', "%{$this->search}%");
        }

        // Filter by language
        if (!empty($this->language)) {
            $query->where('language', $this->language);
        }

        // Filter by tag
        if (!empty($this->tagFilter)) {
            $query->whereHas('tags', function ($q) {
                $q->where('name', $this->tagFilter);
            });
        }

        // Filter by visibility
        if (!empty($this->visibility)) {
            if ($this->visibility === 'public') {
                $query->where('is_public', true);
            } elseif ($this->visibility === 'private') {
                $query->where('is_public', false);
            }
        }

        return $query->with('user', 'tags')->paginate(15);
    }

    public function getLanguagesProperty()
    {
        return Snippet::where('user_id', auth()->id())
            ->select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');
    }

    public function getTagsProperty()
    {
        return Tag::whereHas('snippets', function ($q) {
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
        $this->visibility = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.my-snippets', [
            'snippets' => $this->getSnippetsProperty(),
            'languages' => $this->getLanguagesProperty(),
            'tags' => $this->getTagsProperty(),
        ]);
    }
}
