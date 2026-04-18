<?php

namespace Tests\Unit\Doctor;

use Modules\Doctor\Models\VitalSign;
use Tests\TestCase;

/**
 * `isValueInRange()` is pure domain logic — no DB calls required — but the
 * VitalSign model uses Spatie's HasTranslations trait which reads locale
 * config at boot, so we extend Laravel's base TestCase to avoid surprises.
 */
class VitalSignRangeCheckTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_null_when_value_is_null(): void
    {
        $vital = $this->makeVital(min: 90, max: 120);

        $this->assertNull($vital->isValueInRange(null));
    }

    /**
     * @test
     */
    public function it_returns_null_when_no_range_is_configured(): void
    {
        $vital = $this->makeVital(min: null, max: null);

        $this->assertNull($vital->isValueInRange(120));
    }

    /**
     * @test
     */
    public function it_returns_true_when_value_falls_inside_the_range(): void
    {
        $vital = $this->makeVital(min: 90, max: 120);

        $this->assertTrue($vital->isValueInRange(90));
        $this->assertTrue($vital->isValueInRange(100));
        $this->assertTrue($vital->isValueInRange(120));
    }

    /**
     * @test
     */
    public function it_returns_false_when_value_is_below_min(): void
    {
        $vital = $this->makeVital(min: 90, max: 120);

        $this->assertFalse($vital->isValueInRange(89));
        $this->assertFalse($vital->isValueInRange(0));
    }

    /**
     * @test
     */
    public function it_returns_false_when_value_is_above_max(): void
    {
        $vital = $this->makeVital(min: 90, max: 120);

        $this->assertFalse($vital->isValueInRange(121));
        $this->assertFalse($vital->isValueInRange(6666));
    }

    /**
     * @test
     */
    public function it_handles_min_only_ranges(): void
    {
        $vital = $this->makeVital(min: 50, max: null);

        $this->assertTrue($vital->isValueInRange(60));
        $this->assertTrue($vital->isValueInRange(50));
        $this->assertFalse($vital->isValueInRange(49));
    }

    /**
     * @test
     */
    public function it_handles_max_only_ranges(): void
    {
        $vital = $this->makeVital(min: null, max: 100);

        $this->assertTrue($vital->isValueInRange(80));
        $this->assertTrue($vital->isValueInRange(100));
        $this->assertFalse($vital->isValueInRange(101));
    }

    private function makeVital(?float $min, ?float $max): VitalSign
    {
        $vital = new VitalSign;
        $vital->setRawAttributes([
            'min_value' => $min,
            'max_value' => $max,
        ]);

        return $vital;
    }
}
