<?php

namespace App\Livewire;

use App\Jobs\GenerateSnippetExplanation;
use App\Models\Snippet;
use App\Services\AIExplanationService;
use App\Services\ExplanationRateLimiter;
use Livewire\Component;

class ExplainSnippet extends Component
{
    public Snippet $snippet;
    public bool $isGenerating = false;
    public string $errorMessage = '';
    public int $remainingExplanations = 0;
    public $pollInterval = 5000; // 5 seconds
    public bool $refreshTrigger = false; // Force re-render

    public function mount(AIExplanationService $service, ExplanationRateLimiter $rateLimiter)
    {
        if (auth()->check()) {
            $this->remainingExplanations = $rateLimiter->getRemainingExplanations(auth()->id());
        }
    }

    /**
     * Generate explanation for the snippet.
     */
    public function generateExplanation(AIExplanationService $service, ExplanationRateLimiter $rateLimiter)
    {
        // Check authentication
        if (!auth()->check()) {
            $this->errorMessage = 'You must be logged in to generate explanations.';
            return;
        }

        // Check rate limit
        if (!$rateLimiter->canGenerateExplanation(auth()->id())) {
            $this->errorMessage = 'You have reached your daily limit of explanations. Try again tomorrow.';
            return;
        }

        // Check if explanation already exists
        if ($service->hasExplanation($this->snippet)) {
            $this->errorMessage = 'This snippet already has an explanation. Use regenerate to create a new one.';
            return;
        }

        try {
            $this->isGenerating = true;
            $this->errorMessage = '';

            // Increment the rate limiter
            $rateLimiter->incrementCount(auth()->id());
            $this->remainingExplanations = $rateLimiter->getRemainingExplanations(auth()->id());

            // Dispatch the job
            GenerateSnippetExplanation::dispatch($this->snippet, auth()->id());
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to start explanation generation. Please try again.';
            \Log::error('Error dispatching explanation job', [
                'snippet_id' => $this->snippet->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Regenerate explanation by deleting the old one and creating a new job.
     */
    public function regenerateExplanation(AIExplanationService $service, ExplanationRateLimiter $rateLimiter)
    {
        // Check authentication
        if (!auth()->check()) {
            $this->errorMessage = 'You must be logged in.';
            return;
        }

        // Check rate limit
        if (!$rateLimiter->canGenerateExplanation(auth()->id())) {
            $this->errorMessage = 'You have reached your daily limit of explanations.';
            return;
        }

        try {
            $this->isGenerating = true;
            $this->errorMessage = '';

            // Delete old explanation
            $service->deleteExplanation($this->snippet);

            // Increment the rate limiter
            $rateLimiter->incrementCount(auth()->id());
            $this->remainingExplanations = $rateLimiter->getRemainingExplanations(auth()->id());

            // Dispatch the job
            GenerateSnippetExplanation::dispatch($this->snippet, auth()->id());

            // Reload snippet to clear cached explanation
            $this->snippet->refresh();
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to regenerate explanation. Please try again.';
            \Log::error('Error regenerating explanation', [
                'snippet_id' => $this->snippet->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if explanation is ready (for polling).
     */
    public function checkExplanation(AIExplanationService $service)
    {
        if ($this->isGenerating) {
            // Reload snippet with fresh explanation relationship
            $freshSnippet = Snippet::with('explanation')->find($this->snippet->id);

            if ($freshSnippet && $service->hasExplanation($freshSnippet)) {
                // Update component state
                $this->snippet = $freshSnippet;
                $this->isGenerating = false;
                // Toggle to force Livewire to re-render
                $this->refreshTrigger = !$this->refreshTrigger;
            }
        }
    }

    /**
     * Hide the explanation.
     */
    public function hideExplanation()
    {
        // Component will simply not render the explanation
        // User can refresh or use regenerate button
    }

    public function render()
    {
        // Always reload fresh to ensure we have latest explanation
        $snippet = Snippet::with('explanation')->find($this->snippet->id);
        $explanation = $snippet?->explanation;
        $hasExplanation = (bool) $explanation;

        return view('livewire.explain-snippet', [
            'hasExplanation' => $hasExplanation,
            'explanation' => $explanation,
        ]);
    }
}
