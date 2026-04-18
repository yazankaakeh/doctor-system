<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Shared test bootstrap.
     *
     * `withoutVite()` swaps Laravel's Vite instance with a no-op so every
     * `@vite(...)` directive in a Blade view returns an empty string.
     *
     * Without this, Feature tests that render a view explode with
     * `ViteManifestNotFoundException: Vite manifest not found at
     * public/build/modules/theme/manifest.json` on any machine that has
     * not run `npm run build` — most importantly GitHub Actions, where
     * compiling frontend assets just to render an HTML shell we never
     * assert on would add minutes to every CI run for no real gain.
     *
     * Individual tests that *do* want to verify Vite tag output can still
     * opt in by calling `$this->withVite()` (or just asserting on the
     * raw HTML after setting up the manifest manually).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
