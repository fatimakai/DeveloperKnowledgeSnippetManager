<?php

namespace App\Jobs;

use App\Models\Snippet;
use App\Services\AIExplanationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSnippetExplanation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 5;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Snippet $snippet,
        private int $userId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(AIExplanationService $service): void
    {
        try {
            $service->generateExplanation($this->snippet, $this->userId);
            
            \Log::info('Snippet explanation generated successfully', [
                'snippet_id' => $this->snippet->id,
                'user_id' => $this->userId,
            ]);
        } catch (\Exception $e) {
            \Log::error('Job failed to generate snippet explanation', [
                'snippet_id' => $this->snippet->id,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Snippet explanation job permanently failed', [
            'snippet_id' => $this->snippet->id,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
