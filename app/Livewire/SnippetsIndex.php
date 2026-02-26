<?php

namespace App\Livewire;

use App\Models\Snippet;
use App\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;

class SnippetsIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $language = '';
    public $tagFilter = '';
    public $visibility = '';
    public $sortBy = 'likes'; // 'recent' or 'likes'
    
    protected $listeners = ['snippetDeleted' => 'handleSnippetDeleted'];

    public function updated($property)
    {
        // Reset to first page when any filter changes
        if (in_array($property, ['search', 'language', 'tagFilter', 'visibility', 'sortBy'])) {
            $this->resetPage();
        }
    }

    public function getSnippetsProperty()
    {
        $query = Snippet::where(function ($q) {
            $q->where('is_public', true)
              ->orWhere('user_id', auth()->id());
        });

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

        // Filter by visibility (only for user's own snippets)
        if (!empty($this->visibility)) {
            if ($this->visibility === 'public') {
                $query->where('is_public', true);
            } elseif ($this->visibility === 'private') {
                $query->where([
                    ['is_public', false],
                    ['user_id', auth()->id()]
                ]);
            }
        }

        // Sort by likes or recent
        if ($this->sortBy === 'likes') {
            $query->withCount('likes')
                  ->orderByDesc('likes_count')
                  ->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        return $query->with('user', 'tags')->paginate(15);
    }

    public function getLanguagesProperty()
    {
        return Snippet::where(function ($q) {
            $q->where('is_public', true)
              ->orWhere('user_id', auth()->id());
        })
        ->select('language')
        ->distinct()
        ->orderBy('language')
        ->pluck('language');
    }

    public function getTagsProperty()
    {
        return Tag::whereHas('snippets', function ($q) {
            $q->where(function ($inner) {
                $inner->where('is_public', true)
                      ->orWhere('user_id', auth()->id());
            });
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
        $this->sortBy = 'likes';
        $this->resetPage();
    }

    public function handleSnippetDeleted()
    {
        // Refresh the snippets list when a snippet is deleted
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.snippets-index', [
            'snippets' => $this->getSnippetsProperty(),
            'languages' => $this->getLanguagesProperty(),
            'tags' => $this->getTagsProperty(),
        ]);
    }
}
