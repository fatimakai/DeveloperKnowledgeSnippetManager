<?php

namespace Database\Factories;

use App\Models\Prompt;
use App\Models\PromptReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PromptReport> */
class PromptReportFactory extends Factory
{
    protected $model = PromptReport::class;

    public function definition(): array
    {
        return [
            'prompt_id' => Prompt::factory()->public(),
            'reported_by' => User::factory(),
            'reason' => fake()->randomElement(['spam', 'harmful', 'misleading', 'copyright', 'other']),
            'details' => fake()->optional()->sentence(),
            'status' => PromptReport::STATUS_PENDING,
        ];
    }
}
