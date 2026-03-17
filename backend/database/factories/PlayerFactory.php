<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    /**
     * CS2 roles as listed on HLTV.org.
     *
     * @var list<string>
     */
    private static array $positions = [
        'Rifler',
        'AWPer',
        'IGL',
        'Support',
        'Lurker',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nickname' => fake()->unique()->userName(),
            'country_id' => Country::factory(),
            'name' => fake()->firstName('male'),
            'surname' => fake()->lastName(),
            'date_of_birth' => fake()->dateTimeBetween('-28 years', '-18 years')->format('Y-m-d'),
            'position' => fake()->randomElement(self::$positions),
        ];
    }

    /**
     * Set a specific position state.
     */
    public function position(string $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }
}
