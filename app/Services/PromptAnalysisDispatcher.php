<?php

namespace App\Services;

use App\Exceptions\PromptAnalysisException;
use App\Jobs\AnalyzePromptJob;
use App\Models\Prompt;
use App\Models\PromptAnalysis;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class PromptAnalysisDispatcher
{
    public function __construct(private readonly OpenRouterPromptAnalyzer $analyzer) {}

    public function dispatch(Prompt $prompt, User $user): PromptAnalysis
    {
        if (! $this->analyzer->configured()) {
            throw new PromptAnalysisException('AI analysis is not configured yet. Add your OpenRouter API key and model.', 503);
        }

        $analysis = DB::transaction(function () use ($prompt, $user): PromptAnalysis {
            $lockedPrompt = Prompt::query()->whereKey($prompt->id)->lockForUpdate()->firstOrFail();
            $hasActiveAnalysis = $lockedPrompt->analyses()
                ->whereIn('status', [PromptAnalysis::STATUS_PENDING, PromptAnalysis::STATUS_PROCESSING])
                ->exists();

            if ($hasActiveAnalysis) {
                throw new PromptAnalysisException('An analysis is already in progress for this prompt.', 409);
            }

            $key = $this->rateLimitKey($user);
            $limit = $this->limitFor($user);
            $analysis = RateLimiter::attempt($key, $limit, fn () => $lockedPrompt->analyses()->create([
                'user_id' => $user->id,
                'status' => PromptAnalysis::STATUS_PENDING,
                'provider' => config('prompt-analysis.provider'),
                'model' => config('prompt-analysis.model'),
                'source_prompt_text' => $lockedPrompt->prompt_text,
                'source_target_model' => $lockedPrompt->target_model,
            ]), 3600);

            if (! $analysis) {
                $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));
                throw new PromptAnalysisException("Analysis limit reached. Try again in {$minutes} minute(s).", 429);
            }

            return $analysis;
        });

        try {
            AnalyzePromptJob::dispatch($analysis);
        } catch (Throwable $exception) {
            report($exception);

            $analysis->update([
                'status' => PromptAnalysis::STATUS_FAILED,
                'failure_reason' => 'Analysis could not be queued. Please try again.',
            ]);

            throw new PromptAnalysisException('Analysis could not be queued. Please try again.', 503);
        }

        return $analysis;
    }

    public function remaining(User $user): int
    {
        return RateLimiter::remaining(
            $this->rateLimitKey($user),
            $this->limitFor($user)
        );
    }

    private function rateLimitKey(User $user): string
    {
        return 'prompt-analysis:user:'.$user->id;
    }

    private function limitFor(User $user): int
    {
        return max(1, (int) config($user->isPro() ? 'prompt-analysis.pro_per_hour' : 'prompt-analysis.per_hour'));
    }
}
