<?php

namespace App\Livewire;

use App\Models\Bookmark;
use App\Models\Prompt;
use App\Models\Tag;
use App\Models\Upvote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class PromptBrowser extends Component
{
    use WithPagination;

    public string $mode = 'discover';

    public string $search = '';

    public string $targetModel = '';

    public string $tag = '';

    public string $visibility = '';

    public string $sort = 'popular';

    public function mount(string $mode = 'discover'): void
    {
        abort_unless(in_array($mode, ['discover', 'mine', 'bookmarked'], true), 404);
        abort_if($mode !== 'discover' && ! auth()->check(), 401);
        $this->mode = $mode;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'targetModel', 'tag', 'visibility', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'targetModel', 'tag', 'visibility');
        $this->sort = 'popular';
        $this->resetPage();
    }

    public function toggleUpvote(int $promptId): void
    {
        $user = auth()->user();
        if (! $user) {
            $this->redirectRoute('login');

            return;
        }

        $prompt = Prompt::findOrFail($promptId);
        Gate::authorize('view', $prompt);
        abort_unless($prompt->isPublic(), 422, 'Only public prompts can be upvoted.');

        $existing = Upvote::whereBelongsTo($user)->whereBelongsTo($prompt)->first();
        $existing ? $existing->delete() : Upvote::create(['user_id' => $user->id, 'prompt_id' => $prompt->id]);
    }

    public function toggleBookmark(int $promptId): void
    {
        $user = auth()->user();
        if (! $user) {
            $this->redirectRoute('login');

            return;
        }

        $prompt = Prompt::findOrFail($promptId);
        Gate::authorize('view', $prompt);
        abort_unless($prompt->isPublic(), 422, 'Only public prompts can be bookmarked.');

        $existing = Bookmark::whereBelongsTo($user)->whereBelongsTo($prompt)->first();
        $existing ? $existing->delete() : Bookmark::create(['user_id' => $user->id, 'prompt_id' => $prompt->id]);

        if ($existing && $this->mode === 'bookmarked') {
            $this->resetPage();
        }
    }

    public function deletePrompt(int $promptId): void
    {
        $prompt = Prompt::findOrFail($promptId);
        Gate::authorize('delete', $prompt);
        $prompt->delete();
        session()->flash('success', 'Prompt deleted.');
        $this->resetPage();
    }

    public function render()
    {
        $scope = $this->baseQuery();

        $prompts = (clone $scope)
            ->when($this->search !== '', fn (Builder $query) => $query->where(function (Builder $nested): void {
                $nested->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('prompt_text', 'like', '%'.$this->search.'%');
            }))
            ->when($this->targetModel !== '', fn (Builder $query) => $query->where('target_model', $this->targetModel))
            ->when($this->tag !== '', fn (Builder $query) => $query->whereHas('tags', fn (Builder $tags) => $tags->where('name', $this->tag)))
            ->when($this->visibility !== '' && $this->mode !== 'bookmarked', fn (Builder $query) => $query->where('visibility', $this->visibility))
            ->with(['user', 'tags'])
            ->withCount('upvotes')
            ->when(auth()->check(), function (Builder $query): void {
                $query->withExists([
                    'upvotes as is_upvoted' => fn (Builder $votes) => $votes->where('user_id', auth()->id()),
                    'bookmarks as is_bookmarked' => fn (Builder $bookmarks) => $bookmarks->where('user_id', auth()->id()),
                ]);
            })
            ->when(
                $this->sort === 'popular',
                fn (Builder $query) => $query->orderByDesc('upvotes_count')->latest(),
                fn (Builder $query) => $query->latest()
            )
            ->paginate(12);

        $targets = (clone $scope)->distinct()->orderBy('target_model')->pluck('target_model');
        $tags = Tag::query()->whereHas('prompts', fn (Builder $query) => $this->applyScope($query))->orderBy('name')->get();

        return view('livewire.prompt-browser', compact('prompts', 'targets', 'tags'));
    }

    private function baseQuery(): Builder
    {
        return $this->applyScope(Prompt::query());
    }

    private function applyScope(Builder $query): Builder
    {
        return match ($this->mode) {
            'mine' => $query->where('user_id', auth()->id()),
            'bookmarked' => $query
                ->where('visibility', Prompt::VISIBILITY_PUBLIC)
                ->whereHas('bookmarks', fn (Builder $bookmarks) => $bookmarks->where('user_id', auth()->id())),
            default => $query->where('visibility', Prompt::VISIBILITY_PUBLIC),
        };
    }
}
