<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Smoke test that protects `composer ci` from regression.
 *
 * The `composer ci` script calls four sub-tasks:
 *   - @lint:check  (vendor/bin/pint --test)
 *   - @analyse     (vendor/bin/phpstan analyse)
 *   - @audit       (composer audit)
 *   - @translations(php artisan translations:integrity-check)
 *
 * We can't execute Pint/PHPStan/composer from inside PHPUnit in every
 * environment, but we CAN verify that:
 *   - composer.json still defines all four sub-script references
 *   - the artisan command that the final step invokes is registered
 *   - the phpstan config still exists
 */
class ComposerCiScriptTest extends TestCase
{
    /**
     * @test
     */
    public function composer_json_defines_the_ci_script(): void
    {
        $composer = $this->loadComposerJson();

        $this->assertArrayHasKey('scripts', $composer);
        $this->assertArrayHasKey('ci', $composer['scripts']);
    }

    /**
     * @test
     */
    public function the_ci_script_references_every_expected_step(): void
    {
        $composer = $this->loadComposerJson();
        $ci = $composer['scripts']['ci'] ?? [];

        $this->assertIsArray($ci, 'ci script should be defined as an array of sub-scripts.');

        foreach (['@lint:check', '@analyse', '@audit', '@translations:check'] as $step) {
            $this->assertContains(
                $step,
                $ci,
                "composer ci is missing the `{$step}` step.",
            );
        }
    }

    /**
     * @test
     */
    public function all_referenced_sub_scripts_are_defined(): void
    {
        $composer = $this->loadComposerJson();
        $scripts = $composer['scripts'] ?? [];

        foreach (['lint:check', 'analyse', 'audit', 'translations:check'] as $sub) {
            $this->assertArrayHasKey(
                $sub,
                $scripts,
                "composer ci calls `@{$sub}` but that script is not defined.",
            );
        }
    }

    /**
     * @test
     */
    public function the_translations_artisan_command_is_registered(): void
    {
        // The `@translations` composer step shells out to this artisan
        // command. If the command disappears, `composer ci` will fail.
        $this->assertArrayHasKey(
            'translations:integrity-check',
            Artisan::all(),
            'The translations:integrity-check command is missing but composer ci depends on it.',
        );
    }

    /**
     * @test
     */
    public function phpstan_configuration_file_exists(): void
    {
        // composer analyse runs `vendor/bin/phpstan analyse` which requires
        // the phpstan.neon file at the project root.
        $this->assertFileExists(
            base_path('phpstan.neon'),
            'phpstan.neon is required by the composer `analyse` script.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function loadComposerJson(): array
    {
        $path = base_path('composer.json');
        $this->assertFileExists($path);

        /** @var array<string, mixed> $data */
        $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        return $data;
    }
}
