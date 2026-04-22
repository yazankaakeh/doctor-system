<?php

namespace Tests\Feature\NonFunctional;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;

/**
 * NFR: Portability.
 *
 * Covers:
 *  - System can be deployed on any Linux server or cloud environment.
 *  - Uses widely supported technologies: PHP 8+, Laravel 12, MySQL.
 */
class PortabilityTest extends NonFunctionalTestCase
{
    #[Test]
    public function php_runtime_meets_minimum_version(): void
    {
        $this->assertTrue(
            version_compare(PHP_VERSION, '8.2.0', '>='),
            'Project requires PHP 8.2+ (composer.json pins ^8.4). Current: '.PHP_VERSION
        );
    }

    #[Test]
    public function composer_json_declares_portable_dependencies(): void
    {
        $composer = json_decode(File::get($this->projectRoot().'/composer.json'), true);

        $this->assertArrayHasKey('require', $composer);
        $this->assertArrayHasKey('php', $composer['require']);
        $this->assertArrayHasKey(
            'laravel/framework',
            $composer['require'],
            'laravel/framework must be listed so the project is portable to any PHP host.'
        );

        $this->assertStringContainsString(
            '12.',
            $composer['require']['laravel/framework'],
            'Project should target Laravel 12 as documented.'
        );
    }

    #[Test]
    public function required_php_extensions_are_loaded(): void
    {
        // Pulled from composer.json "require" (ext-*) plus the extensions
        // the Laravel framework itself requires at runtime.
        $required = [
            'curl',
            'mbstring',
            'openssl',
            'pdo',
            'tokenizer',
            'xml',
            'simplexml',
            'ctype',
            'json',
        ];

        $missing = array_filter($required, fn ($ext) => ! extension_loaded($ext));

        $this->assertSame(
            [],
            $missing,
            'Missing PHP extensions required for portability: '.implode(', ', $missing)
        );
    }

    #[Test]
    public function database_layer_supports_portable_drivers(): void
    {
        $connections = config('database.connections');

        // Project must ship wiring for at least MySQL and SQLite so it can
        // move between local dev, Docker, CI, and production cloud hosts.
        $this->assertArrayHasKey('mysql', $connections);
        $this->assertArrayHasKey('sqlite', $connections);
    }

    #[Test]
    public function application_does_not_hard_code_windows_style_paths(): void
    {
        // Scan the Modules/ + app/ trees for hard-coded Windows paths
        // (e.g. `C:\\something`) that would break on Linux hosts.
        $roots = [
            $this->projectRoot().'/app',
            $this->projectRoot().'/Modules',
        ];

        $offenders = [];
        foreach ($roots as $root) {
            if (! File::isDirectory($root)) {
                continue;
            }
            foreach (File::allFiles($root) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                $contents = File::get($file->getPathname());
                // Skip vendor/node/storage - only our own source.
                if (preg_match('/["\']([A-Za-z]:\\\\\\\\)/', $contents)) {
                    $offenders[] = str_replace($this->projectRoot().'/', '', $file->getPathname());
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'The following source files contain Windows-style absolute paths: '.PHP_EOL.implode(PHP_EOL, $offenders)
        );
    }

    #[Test]
    public function docker_recipe_is_available_for_reproducible_deployments(): void
    {
        $hasDockerDir = File::isDirectory($this->projectRoot().'/docker');
        $hasDockerfile = File::exists($this->projectRoot().'/Dockerfile');
        $hasCompose = File::exists($this->projectRoot().'/docker-compose.yml')
            || File::exists($this->projectRoot().'/docker-compose.yaml');

        $this->assertTrue(
            $hasDockerDir || $hasDockerfile || $hasCompose,
            'At least one of docker/ directory, Dockerfile or docker-compose.yml should exist for portable deployment.'
        );
    }

    #[Test]
    public function env_example_is_committed_so_new_environments_can_bootstrap(): void
    {
        $this->assertFileExists(
            $this->projectRoot().'/.env.example',
            '.env.example must exist so the system can be deployed on any host.'
        );
    }
}
