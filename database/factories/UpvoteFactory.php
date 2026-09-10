<?php

namespace Database\Factories;

use App\Models\Prompt;
use App\Models\Upvote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Upvote> */
class UpvoteFactory extends Factory
{
    protected $model = Upvote::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'prompt_id' => Prompt::factory()->public()];
    }
}
