<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * ENC (European Nations Championship) participant countries.
     *
     * @var list<array{name: string, code: string}>
     */
    private static array $countries = [
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
    ];

    public function run(): void
    {
        foreach (self::$countries as $country) {
            Country::firstOrCreate(['code' => $country['code']], $country);
        }
    }
}
