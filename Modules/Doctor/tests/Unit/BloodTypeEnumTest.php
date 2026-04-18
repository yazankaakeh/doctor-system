<?php

namespace Modules\Doctor\Tests\Unit;

use Modules\Doctor\Enums\BloodType;
use PHPUnit\Framework\TestCase;

class BloodTypeEnumTest extends TestCase
{
    /** @test */
    public function it_has_all_blood_type_cases(): void
    {
        $cases = BloodType::cases();

        $this->assertCount(8, $cases);
        $this->assertContains(BloodType::A_POSITIVE, $cases);
        $this->assertContains(BloodType::A_NEGATIVE, $cases);
        $this->assertContains(BloodType::B_POSITIVE, $cases);
        $this->assertContains(BloodType::B_NEGATIVE, $cases);
        $this->assertContains(BloodType::AB_POSITIVE, $cases);
        $this->assertContains(BloodType::AB_NEGATIVE, $cases);
        $this->assertContains(BloodType::O_POSITIVE, $cases);
        $this->assertContains(BloodType::O_NEGATIVE, $cases);
    }

    /** @test */
    public function it_has_correct_integer_values(): void
    {
        $this->assertEquals(1, BloodType::A_POSITIVE->value);
        $this->assertEquals(2, BloodType::A_NEGATIVE->value);
        $this->assertEquals(3, BloodType::B_POSITIVE->value);
        $this->assertEquals(4, BloodType::B_NEGATIVE->value);
        $this->assertEquals(5, BloodType::AB_POSITIVE->value);
        $this->assertEquals(6, BloodType::AB_NEGATIVE->value);
        $this->assertEquals(7, BloodType::O_POSITIVE->value);
        $this->assertEquals(8, BloodType::O_NEGATIVE->value);
    }

    /** @test */
    public function it_can_be_created_from_value(): void
    {
        $bloodType = BloodType::from(1);
        $this->assertEquals(BloodType::A_POSITIVE, $bloodType);

        $bloodType = BloodType::from(7);
        $this->assertEquals(BloodType::O_POSITIVE, $bloodType);
    }

    /** @test */
    public function it_returns_null_for_invalid_value_with_try_from(): void
    {
        $bloodType = BloodType::tryFrom(99);
        $this->assertNull($bloodType);
    }

    /** @test */
    public function it_has_class_method_for_styling(): void
    {
        $this->assertEquals('primary', BloodType::A_POSITIVE->class());
        $this->assertEquals('secondary', BloodType::A_NEGATIVE->class());
        $this->assertEquals('warning', BloodType::B_POSITIVE->class());
        $this->assertEquals('success', BloodType::B_NEGATIVE->class());
        $this->assertEquals('danger', BloodType::O_POSITIVE->class());
        $this->assertEquals('info', BloodType::AB_POSITIVE->class());
        $this->assertEquals('dark', BloodType::AB_NEGATIVE->class());
        $this->assertEquals('warning', BloodType::O_NEGATIVE->class());
    }
}
