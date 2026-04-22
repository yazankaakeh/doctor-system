<?php

namespace Modules\Doctor\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Doctor\Models\Clinic;
use Modules\Doctor\Models\Patient;

class PatientSeeder extends Seeder
{
    /**
     * Total number of patients to seed.
     */
    private const TOTAL_PATIENTS = 500;

    /**
     * Chunk size — keep each batch small enough that memory stays flat and
     * Faker's `unique()` pool doesn't grow unbounded across 500 rows.
     */
    private const CHUNK = 50;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure there is at least a small pool of clinics to attach to.
        if (Clinic::query()->count() === 0) {
            Clinic::factory()->count(5)->create();
        }

        $clinicIds = Clinic::query()->pluck('id')->all();
        $target = self::TOTAL_PATIENTS;
        $chunk = self::CHUNK;

        // Use a single transaction per chunk for throughput; wrap the whole
        // seeder in DB::disableQueryLog() to keep memory flat.
        DB::connection()->disableQueryLog();

        $this->command->getOutput()->progressStart($target);

        for ($created = 0; $created < $target; $created += $chunk) {
            $size = min($chunk, $target - $created);

            DB::transaction(function () use ($size, $clinicIds) {
                Patient::factory()
                    ->count($size)
                    ->create()
                    ->each(function ($patient) use ($clinicIds) {
                        if (empty($clinicIds)) {
                            return;
                        }
                        $take = random_int(1, min(3, count($clinicIds)));
                        $patient->clinics()->attach(
                            collect($clinicIds)->random($take)->all()
                        );
                    });
            });

            $this->command->getOutput()->progressAdvance($size);
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Seeded '.self::TOTAL_PATIENTS.' patients.');
    }
}
