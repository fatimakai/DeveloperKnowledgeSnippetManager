<?php

namespace Database\Factories;

use App\Models\Prompt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Prompt> */
class PromptFactory extends Factory
{
    protected $model = Prompt::class;

    public function definition(): array
    {
        $purposes = [
            'Turn these notes into a concise project update for a technical stakeholder.',
            'Review the supplied copy and suggest clearer, more persuasive alternatives.',
            'Extract the important entities and return them as valid JSON.',
            'Act as a product researcher and summarize the strongest recurring customer needs.',
        ];

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'prompt_text' => fake()->randomElement($purposes)."\n\nContext:\n{{context}}",
            'target_model' => fake()->randomElement(['GPT-4.1', 'GPT-4o', 'Claude Sonnet', 'Gemini 2.5 Pro', 'Model agnostic']),
            'example_input' => fake()->paragraph(),
            'example_output' => fake()->paragraph(),
            'visibility' => fake()->randomElement([Prompt::VISIBILITY_PUBLIC, Prompt::VISIBILITY_PRIVATE]),
        ];
    }

    public function public(): static
    {
        return $this->state(fn () => ['visibility' => Prompt::VISIBILITY_PUBLIC]);
    }

    public function private(): static
    {
        return $this->state(fn () => ['visibility' => Prompt::VISIBILITY_PRIVATE]);
    }
}
