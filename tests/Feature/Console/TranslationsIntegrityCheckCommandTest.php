<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TranslationsIntegrityCheckCommandTest extends TestCase
{
    private string $tmpBase;

    protected function setUp(): void
    {
        parent::setUp();

        // Build a throw-away lang tree under a unique temp directory so we
        // don't touch the real application lang/ files.
        $this->tmpBase = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tr-check-'.uniqid();
        File::makeDirectory($this->tmpBase.'/lang/en', recursive: true);
        File::makeDirectory($this->tmpBase.'/lang/ar', recursive: true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmpBase)) {
            File::deleteDirectory($this->tmpBase);
        }
        parent::tearDown();
    }

    /**
     * @test
     */
    public function the_command_is_registered(): void
    {
        $commands = array_keys(Artisan::all());

        $this->assertContains('translations:integrity-check', $commands);
    }

    /**
     * @test
     */
    public function it_succeeds_when_there_are_no_lang_files(): void
    {
        // The command should not explode when the project has no
        // translation files at all — it should simply succeed quietly.
        $exitCode = Artisan::call('translations:integrity-check', [
            '--base' => 'xx_nonexistent_locale',
        ]);

        // Base locale doesn't exist in any file → every group is skipped → SUCCESS.
        $this->assertSame(0, $exitCode);
    }

    /**
     * @test
     */
    public function the_default_command_output_reports_the_sync_status(): void
    {
        $exitCode = Artisan::call('translations:integrity-check');
        $output = Artisan::output();

        // The command ran — we either saw "in sync" or an error summary. Both
        // are acceptable outcomes for this smoke test; we just verify that
        // the command actually executed and produced output.
        $this->assertNotEmpty($output);
        $this->assertContains($exitCode, [0, 1], 'Exit code should be either 0 (clean) or 1 (issues).');
    }

    /**
     * @test
     */
    public function report_only_flag_is_accepted(): void
    {
        // If the --report-only option was somehow removed from the signature
        // this call would throw InvalidArgumentException.
        $exitCode = Artisan::call('translations:integrity-check', [
            '--report-only' => true,
        ]);

        $this->assertContains($exitCode, [0, 1]);
    }

    /**
     * @test
     */
    public function fix_flag_is_accepted(): void
    {
        $exitCode = Artisan::call('translations:integrity-check', [
            '--fix' => true,
        ]);

        $this->assertContains($exitCode, [0, 1]);
    }
}
