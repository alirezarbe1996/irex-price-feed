<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExchangeSeeder extends Seeder
{
    public function run(): void
    {
        $exchanges = [
            ['name' => 'Arzplus'],
            ['name' => 'Mellichange'],
            ['name' => 'Iranicard'],
            ['name' => 'Nobitex'],
            ['name' => 'Exir'],
            ['name' => 'Ompfinex'],
            ['name' => 'Efex'],
            ['name' => 'Pooleno'],
            ['name' => 'Ramzinex'],
        ];

        DB::table('exchanges')->insert($exchanges);
    }
}
