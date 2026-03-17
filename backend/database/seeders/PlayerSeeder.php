<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Player;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * Real CS2 players grouped by country code.
     * Source: HLTV.org / Liquipedia.
     *
     * @var array<string, list<array{nickname: string, name: string, surname: string, date_of_birth: string, position: string}>>
     */
    private static array $players = [
        'DK' => [
            ['nickname' => 'device',  'name' => 'Nicolai',    'surname' => 'Reedtz',    'date_of_birth' => '1994-09-08', 'position' => 'AWPer'],
            ['nickname' => 'gla1ve',  'name' => 'Lukas',      'surname' => 'Rossander', 'date_of_birth' => '1994-07-25', 'position' => 'IGL'],
            ['nickname' => 'Magisk',  'name' => 'Emil',       'surname' => 'Reif',      'date_of_birth' => '1997-06-14', 'position' => 'Rifler'],
            ['nickname' => 'dupreeh', 'name' => 'Peter',      'surname' => 'Rasmussen', 'date_of_birth' => '1993-01-26', 'position' => 'Rifler'],
            ['nickname' => 'Xyp9x',   'name' => 'Andreas',    'surname' => 'Hojsleth',  'date_of_birth' => '1995-11-05', 'position' => 'Support'],
            ['nickname' => 'blameF',  'name' => 'Benjamin',   'surname' => 'Bremer',    'date_of_birth' => '1996-09-01', 'position' => 'IGL'],
            ['nickname' => 'stavn',   'name' => 'Jonas',      'surname' => 'Stein',     'date_of_birth' => '2001-07-02', 'position' => 'Rifler'],
        ],
        'FR' => [
            ['nickname' => 'ZywOo',    'name' => 'Mathieu',   'surname' => 'Herbaut',    'date_of_birth' => '2000-11-09', 'position' => 'AWPer'],
            ['nickname' => 'apEX',     'name' => 'Dan',       'surname' => 'Madesclaire', 'date_of_birth' => '1992-07-24', 'position' => 'IGL'],
            ['nickname' => 'misutaaa', 'name' => 'Mathieu',   'surname' => 'Quiquerez',  'date_of_birth' => '2001-05-11', 'position' => 'Rifler'],
            ['nickname' => 'JACKZ',    'name' => 'Jack',      'surname' => 'Donerail',   'date_of_birth' => '1995-08-30', 'position' => 'Rifler'],
            ['nickname' => 'bodyy',    'name' => 'Alexandre', 'surname' => 'Pianaro',    'date_of_birth' => '1994-04-21', 'position' => 'Rifler'],
            ['nickname' => 'mezii',    'name' => 'William',   'surname' => 'Merriman',   'date_of_birth' => '2000-06-22', 'position' => 'Rifler'],
        ],
        'UA' => [
            ['nickname' => 's1mple', 'name' => 'Oleksandr', 'surname' => 'Kostyliev',  'date_of_birth' => '1997-10-02', 'position' => 'AWPer'],
            ['nickname' => 'b1t',    'name' => 'Valerii',   'surname' => 'Vakhovskyi', 'date_of_birth' => '2002-06-03', 'position' => 'Rifler'],
            ['nickname' => 'sdy',    'name' => 'Bogdan',    'surname' => 'Zhuk',       'date_of_birth' => '1999-05-19', 'position' => 'Rifler'],
            ['nickname' => 'ANGE1',  'name' => 'Kyrylo',    'surname' => 'Karasiev',   'date_of_birth' => '1991-09-26', 'position' => 'IGL'],
            ['nickname' => 'npl',    'name' => 'Nikolai',   'surname' => 'Neplokho',   'date_of_birth' => '2001-01-28', 'position' => 'Rifler'],
        ],
        'SE' => [
            ['nickname' => 'f0rest',  'name' => 'Patrik',  'surname' => 'Lindberg',  'date_of_birth' => '1988-11-11', 'position' => 'Lurker'],
            ['nickname' => 'REZ',     'name' => 'Fredrik', 'surname' => 'Sterner',   'date_of_birth' => '1997-07-23', 'position' => 'Rifler'],
            ['nickname' => 'KRIMZ',   'name' => 'Freddy',  'surname' => 'Johansson', 'date_of_birth' => '1994-04-05', 'position' => 'Rifler'],
            ['nickname' => 'Plopski', 'name' => 'Nicolas', 'surname' => 'Gonzalez',  'date_of_birth' => '1999-02-21', 'position' => 'Rifler'],
            ['nickname' => 'nawwk',   'name' => 'Joakim',  'surname' => 'Nygard',    'date_of_birth' => '2000-04-03', 'position' => 'AWPer'],
        ],
        'FI' => [
            ['nickname' => 'allu',   'name' => 'Aleksi', 'surname' => 'Jalli',    'date_of_birth' => '1989-01-03', 'position' => 'AWPer'],
            ['nickname' => 'Aerial', 'name' => 'Jere',   'surname' => 'Salo',     'date_of_birth' => '1995-11-28', 'position' => 'Rifler'],
            ['nickname' => 'ZOREE',  'name' => 'Joona',  'surname' => 'Leppanen', 'date_of_birth' => '1998-05-18', 'position' => 'Rifler'],
            ['nickname' => 'hades',  'name' => 'Mikko',  'surname' => 'Vaananen', 'date_of_birth' => '1997-04-18', 'position' => 'IGL'],
            ['nickname' => 'xseveN', 'name' => 'Sami',   'surname' => 'Laasanen', 'date_of_birth' => '1997-01-06', 'position' => 'Support'],
        ],
        'NO' => [
            ['nickname' => 'rain',     'name' => 'Havard',      'surname' => 'Nygaard',   'date_of_birth' => '1994-06-16', 'position' => 'Rifler'],
            ['nickname' => 'jkaem',    'name' => 'Joakim',      'surname' => 'Myrbostad', 'date_of_birth' => '1997-09-24', 'position' => 'Rifler'],
            ['nickname' => 'hallzerk', 'name' => 'Torbjorn',    'surname' => 'Hallmann',  'date_of_birth' => '2001-06-05', 'position' => 'AWPer'],
            ['nickname' => 'Rubino',   'name' => 'Robin',       'surname' => 'Nilsson',   'date_of_birth' => '1993-07-01', 'position' => 'Rifler'],
            ['nickname' => 'Polly',    'name' => 'Christoffer', 'surname' => 'Ramberg',   'date_of_birth' => '1995-02-27', 'position' => 'IGL'],
        ],
        'KZ' => [
            ['nickname' => 'Buster', 'name' => 'Timur',  'surname' => 'Tulepov',       'date_of_birth' => '1997-08-03', 'position' => 'Rifler'],
            ['nickname' => 'SANJI',  'name' => 'Sanjar', 'surname' => 'Durmagambetov', 'date_of_birth' => '1999-05-17', 'position' => 'Support'],
            ['nickname' => 'FL1T',   'name' => 'Evgeny', 'surname' => 'Lebedev',       'date_of_birth' => '1999-04-07', 'position' => 'Rifler'],
            ['nickname' => 'qikert', 'name' => 'Alexey', 'surname' => 'Golubev',       'date_of_birth' => '1999-06-22', 'position' => 'Rifler'],
            ['nickname' => 'AdreN',  'name' => 'Dauren', 'surname' => 'Kystaubayev',   'date_of_birth' => '1990-11-28', 'position' => 'IGL'],
        ],
        'PL' => [
            ['nickname' => 'MICHU',    'name' => 'Michal',  'surname' => 'Muller',     'date_of_birth' => '1995-10-21', 'position' => 'Rifler'],
            ['nickname' => 'innocent', 'name' => 'Pawel',   'surname' => 'Mocek',      'date_of_birth' => '1997-07-12', 'position' => 'Rifler'],
            ['nickname' => 'Hyper',    'name' => 'Mateusz', 'surname' => 'Szlezak',    'date_of_birth' => '1997-05-15', 'position' => 'IGL'],
            ['nickname' => 'F1KU',     'name' => 'Kamil',   'surname' => 'Szkaradek',  'date_of_birth' => '2002-08-11', 'position' => 'Rifler'],
            ['nickname' => 'phr',      'name' => 'Piotr',   'surname' => 'Domagalski', 'date_of_birth' => '2001-10-28', 'position' => 'AWPer'],
        ],
    ];

    public function run(): void
    {
        foreach (self::$players as $countryCode => $players) {
            $country = Country::query()->where('code', $countryCode)->first();

            if ($country === null) {
                continue;
            }

            foreach ($players as $playerData) {
                Player::firstOrCreate(
                    ['nickname' => $playerData['nickname']],
                    [...$playerData, 'country_id' => $country->id],
                );
            }
        }
    }
}
