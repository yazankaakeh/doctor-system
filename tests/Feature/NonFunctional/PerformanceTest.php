<?php

namespace Tests\Feature\NonFunctional;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Doctor\Models\Clinic;
use Modules\Doctor\Models\Patient;
use PHPUnit\Framework\Attributes\Test;

/**
 * NFR: Performance.
 *
 * Covers:
 *  - Fast loading time for medical records and dashboards.
 *  - Efficient MySQL querying using optimized Eloquent relationships.
 *  - Scalable pagination and indexed lookups.
 *
 * These tests run against SQLite in-memory (phpunit.xml), so the latency
 * budgets are deliberately generous. They still catch outright
 * regressions (e.g. accidental full-table loads or missing eager
 * loading) without producing flakes on slow CI agents.
 */
class PerformanceTest extends NonFunctionalTestCase
{
    private const DASHBOARD_BUDGET_MS = 3000;

    private const LISTING_BUDGET_MS = 3000;

    #[Test]
    public function doctor_dashboard_responds_within_budget(): void
    {
        $start = microtime(true);

        $response = $this->actingAsDoctor()->get(route('doctor.dashboard'));

        $elapsedMs = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(
            self::DASHBOARD_BUDGET_MS,
            $elapsedMs,
            "Doctor dashboard rendered in {$elapsedMs}ms, budget is ".self::DASHBOARD_BUDGET_MS.'ms.'
        );
    }

    #[Test]
    public function patient_listing_responds_within_budget_with_realistic_data(): void
    {
        Patient::factory()->count(50)->create([
            'password' => bcrypt('password'),
        ]);

        $start = microtime(true);

        $response = $this->actingAsDoctor()->get(route('doctor.patients.index'));

        $elapsedMs = (microtime(true) - $start) * 1000;

        $response->assertOk();
        $this->assertLessThan(
            self::LISTING_BUDGET_MS,
            $elapsedMs,
            "Patient listing rendered in {$elapsedMs}ms, budget is ".self::LISTING_BUDGET_MS.'ms.'
        );
    }

    #[Test]
    public function patient_listing_uses_pagination_not_full_table_scan(): void
    {
        // Seed more rows than a page's worth to prove pagination is applied.
        Patient::factory()->count(40)->create([
            'password' => bcrypt('password'),
        ]);

        DB::enableQueryLog();

        $this->actingAsDoctor()
            ->get(route('doctor.patients.index'))
            ->assertOk();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $limitedSelects = collect($queries)
            ->filter(fn ($q) => str_contains(strtolower($q['query']), 'from "patients"')
                || str_contains(strtolower($q['query']), 'from `patients`'))
            ->filter(fn ($q) => str_contains(strtolower($q['query']), 'limit'))
            ->count();

        $this->assertGreaterThan(
            0,
            $limitedSelects,
            'Patient listing must use LIMIT (paginate) instead of scanning the entire table.'
        );
    }

    #[Test]
    public function clinic_listing_avoids_n_plus_one_for_media_relations(): void
    {
        // Seed multiple clinics so the index page is non-trivial.
        Clinic::factory()->count(15)->create(['is_active' => 1]);

        DB::enableQueryLog();

        $this->actingAsDoctor()
            ->get(route('doctor.clinic.index'))
            ->assertOk();

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // A classic N+1 would execute ~15+ selects against the clinics /
        // media tables. Allow headroom for middleware, permissions and
        // session warm-up, but fail loudly if it climbs into N+1 territory.
        $this->assertLessThan(
            60,
            $queryCount,
            "Clinic listing issued {$queryCount} queries - likely an N+1 regression."
        );
    }

    #[Test]
    public function commonly_filtered_columns_are_indexed(): void
    {
        // Lookups against patients.email / doctors.email happen on every
        // login and patient search; they must be indexed in the schema.
        $this->assertTrue(Schema::hasColumn('patients', 'email'));
        $this->assertTrue(Schema::hasColumn('doctors', 'email'));

        // The email column must at minimum have a unique index - which is
        // itself the strongest possible "index" for equality lookups.
        $patientIndexes = $this->indexColumns('patients');
        $doctorIndexes = $this->indexColumns('doctors');

        $this->assertContains(
            'email',
            $patientIndexes,
            'patients.email must be indexed for authentication performance.'
        );
        $this->assertContains(
            'email',
            $doctorIndexes,
            'doctors.email must be indexed for authentication performance.'
        );
    }

    #[Test]
    public function cache_store_is_configured(): void
    {
        // Caching is required for scalable dashboards under load.
        $this->assertNotEmpty(config('cache.default'));
        $this->assertArrayHasKey(
            config('cache.default'),
            config('cache.stores'),
            'Default cache store must be defined in config/cache.php'
        );
    }

    /**
     * Return the set of column names that participate in any index on $table.
     *
     * Works for SQLite (testing) and MySQL (runtime) by using the
     * framework's schema introspection helpers.
     */
    private function indexColumns(string $table): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        try {
            // Laravel 11/12 exposes getIndexes() returning ['columns' => [...]]
            $indexes = Schema::getIndexes($table);

            return collect($indexes)
                ->flatMap(fn ($index) => $index['columns'] ?? [])
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
