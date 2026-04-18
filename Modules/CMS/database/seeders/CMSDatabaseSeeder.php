<?php

namespace Modules\CMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CMSDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up old media storage to prevent file mismatch after migrate:fresh
        $this->cleanMediaStorage();

        $this->call([
            MedicalConsultationSeeder::class,
            BookingPanelSeeder::class,
        ]);
    }

    /**
     * Clean media storage directories to prevent file mismatch.
     */
    private function cleanMediaStorage(): void
    {
        $storagePath = storage_path('app/public');

        if (File::isDirectory($storagePath)) {
            $directories = File::directories($storagePath);

            foreach ($directories as $directory) {
                // Only delete numeric directories (media library folders)
                if (is_numeric(basename($directory))) {
                    File::deleteDirectory($directory);
                }
            }

            $this->command->info('Cleaned media storage directories.');
        }
    }
}
