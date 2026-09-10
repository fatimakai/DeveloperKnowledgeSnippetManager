<?php

namespace App\Livewire;

use App\Exceptions\PromptAnalysisException;
use App\Models\Prompt;
use App\Models\PromptAnalysis;
use App\Services\PromptAnalysisDispatcher;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class AnalyzePrompt extends Component
{
    public Prompt $prompt;

    public function mount(Prompt $prompt): void
    {
        Gate::authorize('analyze', $prompt);
        $this->prompt = $prompt;
    }

    public function analyze(PromptAnalysisDispatcher $dispatcher): void
    {
        Gate::authorize('analyze', $this->prompt);
        $this->resetErrorBag('analyze');

        try {
            $dispatcher->dispatch($this->prompt, auth()->user());
        } catch (PromptAnalysisException $exception) {
            $this->addError('analyze', $exception->getMessage());
        }
    }

    public function render()
    {
        $analyses = $this->prompt->analyses()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(5)
            ->get();
        $hasActive = $analyses->contains(
            fn (PromptAnalysis $analysis) => in_array($analysis->status, [PromptAnalysis::STATUS_PENDING, PromptAnalysis::STATUS_PROCESSING], true)
        );
        $remaining = app(PromptAnalysisDispatcher::class)->remaining(auth()->user());

        return view('livewire.analyze-prompt', compact('analyses', 'hasActive', 'remaining'));
    }
}
