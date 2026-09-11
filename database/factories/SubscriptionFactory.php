<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subscription> */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => Subscription::PROVIDER_PAYPAL,
            'provider_subscription_id' => 'I-'.fake()->unique()->bothify('############'),
            'provider_plan_id' => 'P-sandbox-pro-monthly',
            'status' => 'created',
            'amount' => 900,
            'currency' => 'USD',
            'last_synced_at' => now(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => now()->subDay(),
            'current_period_end' => now()->addMonth(),
        ]);
    }
}
