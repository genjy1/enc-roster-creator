<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    /**
     * Real ISO 3166-1 alpha-2 country pairs used for consistent seeding.
     *
     * @var list<array{name: string, code: string}>
     */
    public static array $countries = [
        ['name' => 'Denmark',        'code' => 'DK'],
        ['name' => 'France',         'code' => 'FR'],
        ['name' => 'Ukraine',        'code' => 'UA'],
        ['name' => 'Sweden',         'code' => 'SE'],
        ['name' => 'Finland',        'code' => 'FI'],
        ['name' => 'Norway',         'code' => 'NO'],
        ['name' => 'Kazakhstan',     'code' => 'KZ'],
        ['name' => 'Poland',         'code' => 'PL'],
        ['name' => 'Czech Republic', 'code' => 'CZ'],
        ['name' => 'Slovakia',       'code' => 'SK'],
        ['name' => 'Germany',        'code' => 'DE'],
        ['name' => 'Portugal',       'code' => 'PT'],
        ['name' => 'Estonia',        'code' => 'EE'],
        ['name' => 'Latvia',         'code' => 'LV'],
        ['name' => 'Lithuania',      'code' => 'LT'],
        ['name' => 'Netherlands',    'code' => 'NL'],
        ['name' => 'Croatia',        'code' => 'HR'],
        ['name' => 'Serbia',         'code' => 'RS'],
        ['name' => 'Turkey',         'code' => 'TR'],
        ['name' => 'Georgia',        'code' => 'GE'],
        ['name' => 'Bulgaria',       'code' => 'BG'],
        ['name' => 'Hungary',        'code' => 'HU'],
        ['name' => 'Romania',        'code' => 'RO'],
        ['name' => 'Belgium',        'code' => 'BE'],
        ['name' => 'Austria',        'code' => 'AT'],
        ['name' => 'Spain',          'code' => 'ES'],
        ['name' => 'Italy',          'code' => 'IT'],
        ['name' => 'Switzerland',    'code' => 'CH'],
        ['name' => 'Greece',         'code' => 'GR'],
        ['name' => 'Russia',         'code' => 'RU'],
    ];

    public function definition(): array
    {
        $country = fake()->unique()->randomElement(self::$countries);

        return [
            'name' => $country['name'],
            'code' => $country['code'],
        ];
    }
}
