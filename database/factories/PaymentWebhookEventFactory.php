<?php

namespace Database\Factories;

use App\Models\PaymentWebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PaymentWebhookEvent> */
class PaymentWebhookEventFactory extends Factory
{
    protected $model = PaymentWebhookEvent::class;

    public function definition(): array
    {
        return [
            'provider' => 'paypal',
            'event_id' => fake()->uuid(),
            'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
            'status' => 'received',
            'payload' => [],
        ];
    }
}
