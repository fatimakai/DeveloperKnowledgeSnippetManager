<?php

namespace Database\Factories;

use App\Models\OAuthAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OAuthAccount>
 */
class OAuthAccountFactory extends Factory
{
    protected $model = OAuthAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['google', 'github']),
            'provider_user_id' => fake()->unique()->numerify('##########'),
            'provider_email' => fake()->unique()->safeEmail(),
            'avatar_url' => fake()->imageUrl(200, 200),
        ];
    }
}
