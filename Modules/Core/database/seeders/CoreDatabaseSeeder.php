<?php

namespace Modules\Core\database\Seeders;

use Illuminate\Database\Seeder;

class CoreDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CountriesSeeder::class,
            CitiesSeeder::class,
            StatesSeeder::class,
            ThemeSettingsSeeder::class,
        ]);
    }
}
