<?php

namespace Database\Factories;

use App\Models\CollectionMember;
use App\Models\PromptCollection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CollectionMember> */
class CollectionMemberFactory extends Factory
{
    protected $model = CollectionMember::class;

    public function definition(): array
    {
        return [
            'prompt_collection_id' => PromptCollection::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement([PromptCollection::ROLE_EDITOR, PromptCollection::ROLE_VIEWER]),
            'joined_at' => now(),
        ];
    }
}
