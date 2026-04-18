<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

/**
 * Verifies that every translation file has the same keys across every locale.
 *
 * Scans:
 *   - lang/{locale}/{file}.php                  (app-level)
 *   - Modules/{Module}/lang/{locale}/{file}.php (module-level)
 *
 * Usage:
 *   php artisan translations:integrity-check
 *   php artisan translations:integrity-check --fix
 *   php artisan translations:integrity-check --report-only
 *   php artisan translations:integrity-check --base=en
 */
class TranslationsIntegrityCheck extends Command
{
    protected $signature = 'translations:integrity-check
        {--base=en : The source-of-truth locale to compare all others against}
        {--locales=* : Limit the check to these locale codes (e.g. --locales=en --locales=ar --locales=tr). Defaults to every locale found.}
        {--fix : Fill missing keys in other locales using the base locale values as placeholders}
        {--report-only : Alias for the default behaviour — report issues without attempting to fix}';

    protected $description = 'Verify translation key parity across locales in lang/ and Modules/*/lang/.';

    /**
     * Files that failed to parse (syntax error, NUL byte, malformed array).
     * Tracked so one corrupt file doesn't kill the whole integrity check.
     *
     * @var array<int, array{path: string, error: string}>
     */
    protected array $unparseableFiles = [];

    public function handle(): int
    {
        $baseLocale = (string) $this->option('base');
        $fix = (bool) $this->option('fix');
        $reportOnly = (bool) $this->option('report-only');
        $this->unparseableFiles = [];

        // If the caller specified --locales=en --locales=ar --locales=tr, only
        // those are compared (the base locale is automatically included). If
        // they didn't, we fall back to every locale directory found on disk.
        $allowedLocales = array_values(array_filter((array) $this->option('locales')));
        if ($allowedLocales !== [] && ! in_array($baseLocale, $allowedLocales, true)) {
            $allowedLocales[] = $baseLocale;
        }

        $groups = $this->discoverGroups($allowedLocales);

        if ($groups === []) {
            $this->warn('No translation files found.');

            return self::SUCCESS;
        }

        $problems = 0;
        $keysAdded = 0;
        $filesFixed = 0;

        foreach ($groups as $groupKey => $localeFiles) {
            if (! isset($localeFiles[$baseLocale])) {
                // No base locale to compare against for this group — skip.
                continue;
            }

            $baseData = $this->loadFile($localeFiles[$baseLocale]);
            $baseKeys = $this->flattenKeys($baseData);

            foreach ($localeFiles as $locale => $path) {
                if ($locale === $baseLocale) {
                    continue;
                }

                $translated = $this->loadFile($path);
                $trKeys = $this->flattenKeys($translated);
                $missing = array_values(array_diff($baseKeys, $trKeys));
                $extra = array_values(array_diff($trKeys, $baseKeys));

                if ($missing === [] && $extra === []) {
                    continue;
                }

                $this->line('');
                $this->line("  <fg=cyan>{$groupKey}</> [<fg=green>{$baseLocale}</> → <fg=yellow>{$locale}</>]");

                foreach ($missing as $key) {
                    $this->line("    <fg=red>✗ missing</> {$key}");
                    $problems++;
                }
                foreach ($extra as $key) {
                    $this->line("    <fg=yellow>! extra  </> {$key}");
                    $problems++;
                }

                if ($fix && $missing !== []) {
                    foreach ($missing as $key) {
                        $value = data_get($baseData, $key);
                        data_set($translated, $key, $this->placeholderFor($value, $locale));
                    }

                    $this->writeFile($path, $translated);

                    // The file is now valid (we just wrote a clean version of
                    // it). Drop any earlier parse-error entry for this path so
                    // the final summary doesn't keep flagging it as broken.
                    $this->unparseableFiles = array_values(array_filter(
                        $this->unparseableFiles,
                        fn (array $entry): bool => $entry['path'] !== $path,
                    ));

                    $keysAdded += count($missing);
                    $filesFixed++;
                    $this->line('    <fg=green>✓ fixed</> added '.count($missing).' placeholder key(s)');
                }
            }
        }

        $this->line('');

        // Report any files that couldn't be parsed separately from
        // missing-key issues — these are hard failures that need manual
        // intervention (the file is broken on disk, --fix cannot help).
        if ($this->unparseableFiles !== []) {
            $this->error('  Unparseable translation files ('.count($this->unparseableFiles).'):');
            foreach ($this->unparseableFiles as $entry) {
                $this->line('    <fg=red>✗</> '.$entry['path']);
                $this->line('      '.$entry['error']);
            }
            $this->line('');
        }

        $hasParseErrors = $this->unparseableFiles !== [];

        if ($problems === 0 && ! $hasParseErrors) {
            $this->info('All translation files are in sync.');

            return self::SUCCESS;
        }

        if ($fix && ! $hasParseErrors) {
            $this->info("Fixed {$filesFixed} file(s); added {$keysAdded} placeholder key(s). Please review and translate.");

            return self::SUCCESS;
        }

        if ($reportOnly && ! $hasParseErrors) {
            // --report-only is informational: warn loudly but never break CI
            // for simple missing-key drift. Parse errors still fail though —
            // a broken file is a real bug, not a translation gap.
            $this->warn("{$problems} translation issue(s) found (report-only mode). Run `composer translations:fix` to auto-fill missing keys.");

            return self::SUCCESS;
        }

        if ($hasParseErrors) {
            $this->error('Fix the unparseable translation files above before running the integrity check again.');
        } else {
            $this->error("{$problems} translation issue(s) found. Run `composer translations:fix` to auto-fill missing keys.");
        }

        return self::FAILURE;
    }

    /**
     * Build a map of [namespace::file => [locale => absolutePath]].
     *
     * @param  array<int, string>  $allowedLocales  When non-empty, only these locale codes are included.
     * @return array<string, array<string, string>>
     */
    protected function discoverGroups(array $allowedLocales = []): array
    {
        $groups = [];

        $bases = [
            'app' => base_path('lang'),
        ];

        foreach (glob(base_path('Modules/*/lang'), GLOB_ONLYDIR) ?: [] as $modulePath) {
            $moduleName = basename(dirname($modulePath));
            $bases[$moduleName] = $modulePath;
        }

        foreach ($bases as $namespace => $path) {
            if (! is_dir($path)) {
                continue;
            }

            foreach (scandir($path) ?: [] as $locale) {
                if ($locale === '.' || $locale === '..') {
                    continue;
                }

                // Skip locales not in the allow-list (when one was provided).
                if ($allowedLocales !== [] && ! in_array($locale, $allowedLocales, true)) {
                    continue;
                }

                $localePath = $path.DIRECTORY_SEPARATOR.$locale;
                if (! is_dir($localePath)) {
                    continue;
                }

                $finder = Finder::create()->in($localePath)->files()->name('*.php');
                foreach ($finder as $file) {
                    $relative = substr($file->getRealPath(), strlen($localePath) + 1);
                    $relative = str_replace('\\', '/', $relative);
                    $relative = substr($relative, 0, -4); // strip ".php"

                    $groupKey = "{$namespace}::{$relative}";
                    $groups[$groupKey][$locale] = $file->getRealPath();
                }
            }
        }

        ksort($groups);

        return $groups;
    }

    /**
     * @return array<string, mixed>
     */
    protected function loadFile(string $path): array
    {
        // `require` halts the whole PHP process on a parse error. We wrap it
        // so one broken translation file (stray NUL byte, missing comma, bad
        // closing tag) only flags that file instead of crashing the command.
        try {
            // Pre-flight: read a small slice of the file and reject NUL bytes
            // or other control chars that would make PHP trip before parsing.
            $raw = @file_get_contents($path);
            if ($raw === false) {
                $this->unparseableFiles[] = [
                    'path' => $path,
                    'error' => 'File is not readable',
                ];

                return [];
            }

            if (strpos($raw, "\0") !== false) {
                $this->unparseableFiles[] = [
                    'path' => $path,
                    'error' => 'File contains NUL bytes (0x00) — clean it before running the integrity check',
                ];

                return [];
            }

            $data = require $path;
        } catch (\ParseError $e) {
            $this->unparseableFiles[] = [
                'path' => $path,
                'error' => 'PHP parse error: '.$e->getMessage(),
            ];

            return [];
        } catch (\Throwable $e) {
            $this->unparseableFiles[] = [
                'path' => $path,
                'error' => get_class($e).': '.$e->getMessage(),
            ];

            return [];
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Flatten nested array into dot-notation keys.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    protected function flattenKeys(array $data, string $prefix = ''): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            $full = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (is_array($value)) {
                // If the inner array is an empty array, treat it as a leaf to preserve structure.
                if ($value === []) {
                    $out[] = $full;

                    continue;
                }

                $out = array_merge($out, $this->flattenKeys($value, $full));
            } else {
                $out[] = $full;
            }
        }

        return $out;
    }

    /**
     * Build a placeholder value for --fix. Keeps the base value but prefixes
     * with the locale code so missing translations are obvious at a glance.
     */
    protected function placeholderFor(mixed $baseValue, string $locale): mixed
    {
        if (is_string($baseValue)) {
            return "[TODO:{$locale}] {$baseValue}";
        }

        return $baseValue;
    }

    /**
     * Write a translation file back to disk using a Laravel-friendly export
     * format (short array syntax, no class names in var_export output).
     *
     * @param  array<string, mixed>  $data
     */
    protected function writeFile(string $path, array $data): void
    {
        $export = $this->varExport($data);
        $content = "<?php\n\nreturn {$export};\n";
        file_put_contents($path, $content);
    }

    /**
     * Pretty var_export using PHP 5.4+ short-array syntax.
     */
    protected function varExport(mixed $value, int $indent = 0): string
    {
        if (is_array($value)) {
            $pad = str_repeat('    ', $indent);
            $inner = str_repeat('    ', $indent + 1);

            if ($value === []) {
                return '[]';
            }

            $isAssoc = array_keys($value) !== range(0, count($value) - 1);
            $lines = [];

            foreach ($value as $k => $v) {
                $line = $inner;
                if ($isAssoc) {
                    $line .= var_export($k, true).' => ';
                }
                $line .= $this->varExport($v, $indent + 1).',';
                $lines[] = $line;
            }

            return "[\n".implode("\n", $lines)."\n{$pad}]";
        }

        return var_export($value, true);
    }
}
