<?php

namespace Database\Factories;

use App\Models\PromptCollection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PromptCollection> */
class PromptCollectionFactory extends Factory
{
    protected $model = PromptCollection::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
