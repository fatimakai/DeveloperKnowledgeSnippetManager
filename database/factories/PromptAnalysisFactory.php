<?php

namespace Database\Factories;

use App\Models\Prompt;
use App\Models\PromptAnalysis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PromptAnalysis> */
class PromptAnalysisFactory extends Factory
{
    protected $model = PromptAnalysis::class;

    public function definition(): array
    {
        return [
            'prompt_id' => Prompt::factory(),
            'user_id' => User::factory(),
            'status' => PromptAnalysis::STATUS_PENDING,
            'provider' => 'openrouter',
            'model' => 'test/model',
            'source_prompt_text' => fake()->paragraph(),
            'source_target_model' => 'GPT-4.1',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => PromptAnalysis::STATUS_COMPLETED,
            'analysis' => [
                'intent_summary' => 'Create a concise customer update.',
                'weaknesses' => ['The desired output format is not specified.'],
                'improved_prompt' => 'Create a concise customer update using three bullet points.',
            ],
            'completed_at' => now(),
        ]);
    }
}
