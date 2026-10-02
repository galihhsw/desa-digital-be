<?php

namespace Database\Factories;

use App\Models\SocialAssistanceRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialAssistanceRecipient>
 */
class SocialAssistanceRecipientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 10000, 100000),
            'reason' => $this->faker->sentence(),
            'bank' => $this->faker->randomElement(['bri', 'bni', 'mandiri', 'bca']),
            'account_number' => $this->faker->unique()->numberBetween(10000000, 99999999),
            'proof' => $this->faker->url(),
            'status' => $this->faker->randomElement(['Pending', 'Approved', 'Rejected']),
            
        ];
    }
}


