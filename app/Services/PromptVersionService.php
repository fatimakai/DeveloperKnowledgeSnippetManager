<?php

namespace App\Services;

use App\Models\Prompt;
use App\Models\PromptVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PromptVersionService
{
    public const VERSIONED_FIELDS = [
        'title',
        'description',
        'prompt_text',
        'target_model',
        'example_input',
        'example_output',
        'visibility',
        'tags',
    ];

    private const FIELD_LABELS = [
        'title' => 'title',
        'description' => 'description',
        'prompt_text' => 'prompt text',
        'target_model' => 'target model',
        'example_input' => 'example input',
        'example_output' => 'example output',
        'visibility' => 'visibility',
        'tags' => 'tags',
    ];

    public function record(Prompt $prompt, User $actor, ?string $summary = null): ?PromptVersion
    {
        if (! $actor->isPro()) {
            return null;
        }

        return DB::transaction(function () use ($prompt, $actor, $summary): PromptVersion {
            $lockedPrompt = Prompt::query()
                ->whereKey($prompt->getKey())
                ->lockForUpdate()
                ->firstOrFail()
                ->load('tags');

            $snapshot = $this->snapshot($lockedPrompt);
            $latest = $lockedPrompt->versions()->latest('version_number')->first();

            if ($latest && $latest->snapshot() === $snapshot) {
                return $latest;
            }

            return $lockedPrompt->versions()->create([
                ...$snapshot,
                'created_by' => $actor->id,
                'version_number' => ($latest?->version_number ?? 0) + 1,
                'change_summary' => $summary ?? $this->changeSummary($latest, $snapshot),
            ]);
        });
    }

    public function snapshot(Prompt $prompt): array
    {
        $prompt->loadMissing('tags');

        return [
            'title' => $prompt->title,
            'description' => $prompt->description,
            'prompt_text' => $prompt->prompt_text,
            'target_model' => $prompt->target_model,
            'example_input' => $prompt->example_input,
            'example_output' => $prompt->example_output,
            'visibility' => $prompt->visibility,
            'tags' => $prompt->tags->pluck('name')->sort()->values()->all(),
        ];
    }

    private function changeSummary(?PromptVersion $latest, array $snapshot): string
    {
        if (! $latest) {
            return 'Initial version';
        }

        $changed = collect(self::VERSIONED_FIELDS)
            ->filter(fn (string $field) => $latest->{$field} !== $snapshot[$field])
            ->map(fn (string $field) => self::FIELD_LABELS[$field])
            ->values();

        return 'Updated '.$changed->join(', ', ' and ');
    }
}
