<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = 'The ' . $this->faker->lastName() . ' Property';
        $slug = Str::slug($name);
        return [
            'name' => $name,
            'slug' => $slug,
            'state' => $this->faker->country(),
            'type' => $this->faker->randomElements(['flat', 'house', 'land'], $count = 1, $allowDuplicates = false)[0],
            'category' => $this->faker->randomElements(['buy', 'rent', 'shortlet'], $count = 1, $allowDuplicates = false)[0],
            'bedrooms' => $this->faker->numerify('#'),
            'toilets' => $this->faker->numerify('#'),
            'price' => $this->faker->numerify('######'),
            'payment_frequency' => $this->faker->randomElements(['daily', 'weekly', 'monthly', 'annually', 'outright'], $count = 1, $allowDuplicates = false)[0],
            'address' => $this->faker->address(),
        ];
    }
}
