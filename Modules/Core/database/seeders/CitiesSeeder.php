<?php

namespace Modules\Core\database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('Modules/Core/database/sql/cities.sql');

        if (! File::exists($path)) {
            $this->command->error("❌ cities.sql not found at: $path");

            return;
        }

        $this->command->info('🌍 Importing Cities...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::unprepared(File::get($path));
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Cities imported successfully!');
    }
}
