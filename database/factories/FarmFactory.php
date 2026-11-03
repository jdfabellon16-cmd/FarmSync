<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Farm>
 */
class FarmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->company().' Farm',
            'location' => fake()->city().', '.fake()->state(),
            'latitude' => fake()->latitude(-35, 35),
            'longitude' => fake()->longitude(-120, 150),
            'total_area_hectares' => fake()->randomFloat(4, 5, 800),
        ];
    }
}
