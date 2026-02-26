<?php

namespace App\Livewire;

use App\Models\Like;
use App\Models\Snippet;
use Livewire\Component;

class LikeSnippet extends Component
{
    public Snippet $snippet;

    public function toggleLike()
    {
        if (!auth()->check()) {
            return $this->redirect('/login');
        }

        $userId = auth()->id();
        $existingLike = Like::where('user_id', $userId)
            ->where('snippet_id', $this->snippet->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            Like::create([
                'user_id' => $userId,
                'snippet_id' => $this->snippet->id,
            ]);
        }

        // Refresh the snippet to update counts
        $this->snippet->refresh();
        $this->dispatch('snippetLiked');
    }

    public function getLikesCount()
    {
        return $this->snippet->likes()->count();
    }

    public function getIsLiked()
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->snippet->likedByUser(auth()->id());
    }

    public function render()
    {
        return view('livewire.like-snippet', [
            'likesCount' => $this->getLikesCount(),
            'isLiked' => $this->getIsLiked(),
        ]);
    }
}
