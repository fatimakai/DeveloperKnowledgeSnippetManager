<?php

namespace App\Livewire;

use App\Models\Snippet;
use Livewire\Component;

class DeleteSnippet extends Component
{
    public $snippet;
    public $showConfirmation = false;
    
    public function mount(Snippet $snippet)
    {
        $this->snippet = $snippet;
    }
    
    public function openConfirmation()
    {
        $this->showConfirmation = true;
    }
    
    public function closeConfirmation()
    {
        $this->showConfirmation = false;
    }
    
    public function delete()
    {
        // Authorization check - user can only delete their own snippets
        if ($this->snippet->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $title = $this->snippet->title;
        $this->snippet->delete();
        
        session()->flash('success', "Snippet '{$title}' deleted successfully!");
        return redirect()->route('snippets.my');
    }
    
    public function render()
    {
        return view('livewire.delete-snippet');
    }
}
