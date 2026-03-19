<?php

namespace Database\Seeders;

use App\Models\Country;
use Database\Factories\CountryFactory;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        foreach (CountryFactory::$countries as $country) {
            Country::firstOrCreate(['code' => $country['code']], ['name' => $country['name']]);
        }
    }
}
