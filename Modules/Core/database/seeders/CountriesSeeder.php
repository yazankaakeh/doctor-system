<?php

namespace Modules\Core\database\Seeders;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @throws FileNotFoundException
     */
    public function run(): void
    {
        $path = base_path('Modules/Core/database/sql/countries.sql');

        if (! File::exists($path)) {
            $this->command->error("❌ SQL file not found at {$path}");

            return;
        }
        $this->command->info('🌍 Importing countries...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::unprepared(File::get($path));
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Countries imported successfully from SQL.');
    }
}
