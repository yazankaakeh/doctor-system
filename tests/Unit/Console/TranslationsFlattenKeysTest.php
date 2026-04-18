<?php

namespace Tests\Unit\Console;

use App\Console\Commands\TranslationsIntegrityCheck;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * The command's `flattenKeys()` is the core of the integrity checker — if
 * two locales flatten to different key lists, the checker fails. This test
 * pins its behaviour without booting Laravel.
 */
class TranslationsFlattenKeysTest extends TestCase
{
    private function flatten(array $data): array
    {
        $cmd = new TranslationsIntegrityCheck;
        $m = new ReflectionMethod($cmd, 'flattenKeys');
        $m->setAccessible(true);

        /** @var array<int, string> $flat */
        $flat = $m->invoke($cmd, $data, '');

        return $flat;
    }

    /**
     * @test
     */
    public function it_flattens_a_simple_associative_array(): void
    {
        $keys = $this->flatten([
            'name' => 'Name',
            'email' => 'Email',
        ]);

        $this->assertEqualsCanonicalizing(['name', 'email'], $keys);
    }

    /**
     * @test
     */
    public function it_flattens_nested_keys_with_dot_notation(): void
    {
        $keys = $this->flatten([
            'profile' => [
                'name' => 'Name',
                'email' => 'Email',
            ],
            'button' => [
                'save' => 'Save',
                'cancel' => 'Cancel',
            ],
        ]);

        $this->assertEqualsCanonicalizing(
            ['profile.name', 'profile.email', 'button.save', 'button.cancel'],
            $keys,
        );
    }

    /**
     * @test
     */
    public function it_flattens_deeply_nested_keys(): void
    {
        $keys = $this->flatten([
            'enum' => [
                'Status' => [
                    1 => 'Pending',
                    2 => 'Confirmed',
                ],
            ],
        ]);

        $this->assertEqualsCanonicalizing(
            ['enum.Status.1', 'enum.Status.2'],
            $keys,
        );
    }

    /**
     * @test
     */
    public function it_treats_empty_arrays_as_leaves_so_missing_subtrees_are_detected(): void
    {
        $keys = $this->flatten([
            'empty_section' => [],
            'filled' => [
                'one' => '1',
            ],
        ]);

        $this->assertEqualsCanonicalizing(['empty_section', 'filled.one'], $keys);
    }

    /**
     * @test
     */
    public function the_diff_of_two_key_lists_is_stable(): void
    {
        $base = $this->flatten(['a' => 1, 'b' => ['x' => 1, 'y' => 2]]);
        $trans = $this->flatten(['a' => 1, 'b' => ['x' => 1]]);

        $missing = array_values(array_diff($base, $trans));
        $this->assertSame(['b.y'], $missing);
    }
}
