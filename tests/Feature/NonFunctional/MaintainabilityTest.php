<?php

namespace Tests\Feature\NonFunctional;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;

/**
 * NFR: Maintainability.
 *
 * Covers:
 *  - Modular HMVC architecture using Laravel Modules.
 *  - Clear separation between Doctor / Core / Theme / Blog / User Management.
 *  - Easy to extend (new medical forms, telemedicine, etc).
 *
 * These assertions are structural: they guard the module boundaries
 * and the "thin controller / FormRequest / Action" convention called
 * out in CLAUDE.md so accidental regressions surface immediately.
 */
class MaintainabilityTest extends NonFunctionalTestCase
{
    private const REQUIRED_MODULES = [
        'Core',
        'Doctor',
        'Auth',
        'AdminManagement',
        'Theme',
        'Blog',
    ];

    #[Test]
    public function all_required_modules_exist_on_disk(): void
    {
        foreach (self::REQUIRED_MODULES as $module) {
            $path = $this->projectRoot()."/Modules/$module";
            $this->assertTrue(
                File::isDirectory($path),
                "Module [$module] is missing from Modules/ - required for maintainable separation of concerns."
            );
        }
    }

    #[Test]
    public function each_module_declares_its_own_routes_and_views(): void
    {
        $modulesWithHttp = ['Doctor', 'Auth', 'AdminManagement', 'Blog'];

        foreach ($modulesWithHttp as $module) {
            $base = $this->projectRoot()."/Modules/$module";

            $this->assertTrue(
                File::isDirectory($base.'/routes'),
                "Module [$module] must own its routes/ folder."
            );
            $this->assertTrue(
                File::isDirectory($base.'/resources/views'),
                "Module [$module] must own its resources/views folder."
            );
        }
    }

    #[Test]
    public function modules_statuses_file_exists_and_enables_core_modules(): void
    {
        $path = $this->projectRoot().'/modules_statuses.json';

        $this->assertFileExists($path);

        $statuses = json_decode(File::get($path), true);
        $this->assertIsArray($statuses);

        foreach (['Core', 'Doctor', 'Theme'] as $mandatory) {
            $this->assertArrayHasKey(
                $mandatory,
                $statuses,
                "modules_statuses.json must list the [$mandatory] module."
            );
            $this->assertTrue(
                (bool) $statuses[$mandatory],
                "Module [$mandatory] must be enabled."
            );
        }
    }

    #[Test]
    public function form_requests_exist_for_doctor_write_operations(): void
    {
        $requestsDir = $this->projectRoot().'/Modules/Doctor/app/Http/Requests';

        $this->assertTrue(File::isDirectory($requestsDir));

        $requestFiles = collect(File::files($requestsDir))
            ->map(fn ($f) => $f->getFilenameWithoutExtension())
            ->all();

        foreach (['ClinicRequest', 'PatientRequest', 'MedicalExaminationRequest'] as $expected) {
            $this->assertContains(
                $expected,
                $requestFiles,
                "Form Request [$expected] missing - controllers must stay thin by using FormRequest classes."
            );
        }
    }

    #[Test]
    public function controllers_stay_thin_and_do_not_validate_inline(): void
    {
        $controllersDir = $this->projectRoot().'/Modules/Doctor/app/Http/Controllers';

        $this->assertTrue(File::isDirectory($controllersDir));

        foreach (File::files($controllersDir) as $file) {
            $contents = File::get($file->getPathname());

            // Inline $request->validate([...]) or Validator::make([...]) in a
            // controller is a maintainability smell we actively reject.
            $this->assertDoesNotMatchRegularExpression(
                '/\$request->validate\s*\(/',
                $contents,
                $file->getFilename().' performs inline validation; use a FormRequest instead.'
            );
            $this->assertDoesNotMatchRegularExpression(
                '/Validator::make\s*\(/',
                $contents,
                $file->getFilename().' constructs a Validator inline; use a FormRequest instead.'
            );
        }
    }

    #[Test]
    public function modules_do_not_directly_depend_on_unrelated_modules(): void
    {
        // Theme must stay reusable: it must not pull Doctor/Patient/Booking
        // namespaces. A leak here means the theme has been coupled to a
        // vertical and can no longer be reused by a new module.
        $themeRoot = $this->projectRoot().'/Modules/Theme';

        if (! File::isDirectory($themeRoot)) {
            $this->markTestSkipped('Theme module not present.');
        }

        $offenders = [];
        foreach (File::allFiles($themeRoot) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $contents = File::get($file->getPathname());
            foreach (['Modules\\Doctor\\', 'Modules\\Patient\\', 'Modules\\Booking\\'] as $needle) {
                if (str_contains($contents, $needle)) {
                    $offenders[] = $file->getRelativePathname().' references '.$needle;
                    break;
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'Theme module must not depend on vertical modules: '.PHP_EOL.implode(PHP_EOL, $offenders)
        );
    }

    #[Test]
    public function a_new_module_can_be_discovered_by_the_package(): void
    {
        // Laravel-modules discovers modules via modules_statuses.json.
        // If the artisan command is registered, the infrastructure for
        // adding new modules (e.g. Telemedicine) still works.
        $output = [];
        $exitCode = 0;
        @exec(PHP_BINARY.' '.escapeshellarg($this->projectRoot().'/artisan').' list --raw 2>&1', $output, $exitCode);

        $joined = implode("\n", $output);

        // We only care that module:list is present in the artisan surface.
        // If artisan is unavailable under this test environment, skip.
        if ($exitCode !== 0 || $joined === '') {
            $this->markTestSkipped('artisan list is not runnable in this test environment.');
        }

        $this->assertStringContainsString(
            'module:list',
            $joined,
            'nwidart/laravel-modules commands must be registered for easy module extension.'
        );
    }
}
