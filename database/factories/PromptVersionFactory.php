<?php

namespace Database\Factories;

use App\Models\Prompt;
use App\Models\PromptVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PromptVersion> */
class PromptVersionFactory extends Factory
{
    protected $model = PromptVersion::class;

    public function definition(): array
    {
        return [
            'prompt_id' => Prompt::factory(),
            'created_by' => User::factory(),
            'version_number' => 1,
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'prompt_text' => fake()->paragraph(),
            'target_model' => fake()->randomElement(['GPT-4.1', 'Claude Sonnet', 'Gemini 2.5 Pro']),
            'example_input' => fake()->paragraph(),
            'example_output' => fake()->paragraph(),
            'visibility' => Prompt::VISIBILITY_PRIVATE,
            'tags' => ['research'],
            'change_summary' => 'Initial version',
        ];
    }
}
