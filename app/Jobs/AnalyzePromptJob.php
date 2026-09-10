<?php

namespace App\Jobs;

use App\Models\PromptAnalysis;
use App\Services\OpenRouterPromptAnalyzer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AnalyzePromptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public PromptAnalysis $promptAnalysis) {}

    public function backoff(): array
    {
        return [10, 30];
    }

    public function handle(OpenRouterPromptAnalyzer $analyzer): void
    {
        $this->promptAnalysis->update([
            'status' => PromptAnalysis::STATUS_PROCESSING,
            'failure_reason' => null,
            'completed_at' => null,
        ]);

        try {
            $result = $analyzer->analyze(
                $this->promptAnalysis->source_prompt_text,
                $this->promptAnalysis->source_target_model,
            );

            $this->promptAnalysis->update([
                ...$result,
                'status' => PromptAnalysis::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $this->promptAnalysis->update([
                'status' => PromptAnalysis::STATUS_FAILED,
                'failure_reason' => (string) str($exception->getMessage())->limit(2000),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->promptAnalysis->update([
            'status' => PromptAnalysis::STATUS_FAILED,
            'failure_reason' => 'Analysis failed after multiple attempts. Please try again.',
        ]);
    }
}
