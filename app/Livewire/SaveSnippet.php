<?php

namespace App\Livewire;

use App\Models\SavedSnippet;
use App\Models\Snippet;
use Livewire\Component;

class SaveSnippet extends Component
{
    public Snippet $snippet;

    public function toggleSave()
    {
        if (!auth()->check()) {
            return $this->redirect('/login');
        }

        $userId = auth()->id();
        $existingSave = SavedSnippet::where('user_id', $userId)
            ->where('snippet_id', $this->snippet->id)
            ->first();

        if ($existingSave) {
            $existingSave->delete();
        } else {
            SavedSnippet::create([
                'user_id' => $userId,
                'snippet_id' => $this->snippet->id,
            ]);
        }

        // Refresh the snippet to update counts
        $this->snippet->refresh();
        $this->dispatch('snippetSaved');
    }

    public function getIsSaved()
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->snippet->savedByUser(auth()->id());
    }

    public function render()
    {
        return view('livewire.save-snippet', [
            'isSaved' => $this->getIsSaved(),
        ]);
    }
}
