<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thumbnail' => $this->faker->imageUrl(),
            'name' => $this->faker->randomElement(['Jalan Sehat', 'Kerja Bakti', 'Lomba Volley', 'Hajatan']),
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10000, 100000),
            'date' => $this->faker->dateTimeBetween('-1year', 'now'),
            'time' => $this->faker->dateTimeBetween,
            'is_active' => $this->faker->boolean(),
        ];
    }
}
