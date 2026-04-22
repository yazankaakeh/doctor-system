<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AdminManagement\database\seeders\AdminManagementDatabaseSeeder;
use Modules\Booking\Database\Seeders\BookingDatabaseSeeder;
use Modules\CMS\Database\Seeders\CMSDatabaseSeeder;
use Modules\Core\Database\Seeders\CoreDatabaseSeeder;
use Modules\Doctor\Database\Seeders\DoctorDatabaseSeeder;
use Modules\Seo\Database\Seeders\SeoDatabaseSeeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminManagementDatabaseSeeder::class,
            CoreDatabaseSeeder::class,
            DoctorDatabaseSeeder::class,
            SeoDatabaseSeeder::class,
            CMSDatabaseSeeder::class,
            // Must run AFTER DoctorDatabaseSeeder — seeds recurring
            // schedules for each doctor and materializes availability
            // rows for the next 30 days so bookings are possible.
            BookingDatabaseSeeder::class,
        ]);
    }
}
