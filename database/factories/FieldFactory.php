<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Field;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Field>
 */
class FieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'name' => 'Field '.fake()->unique()->numberBetween(1, 9999),
            'area_hectares' => fake()->randomFloat(4, 0.1, 80),
            'soil_type' => fake()->randomElement(['Loam', 'Sandy loam', 'Clay loam']),
            'irrigation_type' => fake()->randomElement(['Drip', 'Sprinkler', 'Rain-fed']),
            'status' => 'Fallow',
            'latitude' => fake()->latitude(-35, 35),
            'longitude' => fake()->longitude(-120, 150),
        ];
    }
}
