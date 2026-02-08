<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Platform>
 */
class PlatformFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->word(),
            'proficiency' => $this->faker->randomElement([\App\Enums\Proficiency::BEGINNER, \App\Enums\Proficiency::INTERMEDIATE, \App\Enums\Proficiency::EXPERT]),
        ];
    }
}
