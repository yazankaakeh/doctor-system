<?php

namespace Tests\Feature\NonFunctional;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

/**
 * NFR: Availability & Reliability.
 *
 * Covers:
 *  - Consistent up time for daily clinical operations.
 *  - Automatic backups of database and uploaded files.
 *  - Graceful recovery after system failure.
 */
class AvailabilityReliabilityTest extends NonFunctionalTestCase
{
    // ---------------------------------------------------------------------
    // Graceful failure handling
    // ---------------------------------------------------------------------

    #[Test]
    public function unknown_routes_return_404_not_a_500(): void
    {
        $response = $this->get('/definitely-not-a-real-route-'.uniqid());

        $this->assertSame(
            404,
            $response->status(),
            'Unknown routes must respond with 404, never a server error.'
        );
    }

    #[Test]
    public function accessing_missing_resource_returns_404(): void
    {
        $response = $this->actingAsDoctor()
            ->post(route('doctor.clinic.update'), [
                'id' => 999999,
                'name' => 'Does not exist',
                'is_active' => 1,
            ]);

        // findOrFail => 404 is the expected graceful path.
        $this->assertSame(404, $response->status());
    }

    #[Test]
    public function application_recovers_after_exception_and_remains_responsive(): void
    {
        // Simulate a failed request followed by a healthy request.
        $this->get('/definitely-not-a-real-route-'.uniqid())
            ->assertStatus(404);

        $response = $this->actingAsDoctor()->get(route('doctor.dashboard'));

        $response->assertOk();
    }

    #[Test]
    public function logging_stack_is_configured(): void
    {
        $this->assertNotEmpty(config('logging.default'));
        $this->assertArrayHasKey(
            config('logging.default'),
            config('logging.channels'),
            'Default logging channel must be defined.'
        );

        // Smoke test: writing a log entry must not throw.
        Log::info('NFR availability smoke test log entry.');
        $this->assertTrue(true);
    }

    // ---------------------------------------------------------------------
    // Backup readiness
    // ---------------------------------------------------------------------

    #[Test]
    public function database_connections_are_configured_for_restorable_engines(): void
    {
        $default = config('database.default');
        $driver = config("database.connections.$default.driver");

        $this->assertContains(
            $driver,
            ['mysql', 'pgsql', 'sqlite', 'mariadb'],
            "Unsupported DB driver [$driver]; backup tooling must target a known engine."
        );
    }

    #[Test]
    public function storage_disks_are_defined_so_uploaded_files_can_be_backed_up(): void
    {
        $disks = config('filesystems.disks');

        $this->assertIsArray($disks);
        $this->assertArrayHasKey('local', $disks);
        $this->assertArrayHasKey('public', $disks);
    }

    #[Test]
    public function storage_directories_exist_and_are_writable(): void
    {
        $paths = [
            storage_path('app'),
            storage_path('logs'),
            storage_path('framework'),
        ];

        foreach ($paths as $path) {
            if (! File::isDirectory($path)) {
                // If a required storage dir is missing the test should fail
                // loudly: a fresh deploy that forgot chmod will break jobs.
                $this->fail("Required storage directory missing: $path");
            }

            $this->assertTrue(
                is_writable($path),
                "Storage path [$path] must be writable for logs / queued jobs / backups."
            );
        }
    }

    // ---------------------------------------------------------------------
    // Migration / schema integrity (crucial for graceful recovery)
    // ---------------------------------------------------------------------

    #[Test]
    public function core_clinical_tables_exist_after_migrations(): void
    {
        $tables = [
            'doctors',
            'patients',
            'clinics',
            'medical_examinations',
            'roles',
            'permissions',
            'password_reset_tokens',
            'sessions',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                // Sessions may not exist when session.driver !== database.
                if ($table === 'sessions') {
                    continue;
                }
                $this->fail("Required table [$table] missing - recovery from migrations is broken.");
            }
        }

        $this->assertTrue(true);
    }

    #[Test]
    public function queue_connection_is_configured_for_async_reliability(): void
    {
        $connection = config('queue.default');

        $this->assertNotEmpty($connection);
        $this->assertArrayHasKey(
            $connection,
            config('queue.connections'),
            'Default queue connection must be defined.'
        );
    }
}
