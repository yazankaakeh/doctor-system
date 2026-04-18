<?php

namespace Modules\Booking\Tests\Unit;

use Modules\Booking\Enums\BookingStatusEnum;
use PHPUnit\Framework\TestCase;

class BookingStatusEnumTest extends TestCase
{
    /** @test */
    public function it_has_all_status_cases(): void
    {
        $cases = BookingStatusEnum::cases();

        $this->assertCount(5, $cases);
        $this->assertContains(BookingStatusEnum::PENDING, $cases);
        $this->assertContains(BookingStatusEnum::CONFIRMED, $cases);
        $this->assertContains(BookingStatusEnum::CANCELLED, $cases);
        $this->assertContains(BookingStatusEnum::COMPLETED, $cases);
        $this->assertContains(BookingStatusEnum::NO_SHOW, $cases);
    }

    /** @test */
    public function it_has_correct_integer_values(): void
    {
        $this->assertEquals(1, BookingStatusEnum::PENDING->value);
        $this->assertEquals(2, BookingStatusEnum::CONFIRMED->value);
        $this->assertEquals(3, BookingStatusEnum::CANCELLED->value);
        $this->assertEquals(4, BookingStatusEnum::COMPLETED->value);
        $this->assertEquals(5, BookingStatusEnum::NO_SHOW->value);
    }

    /** @test */
    public function it_can_be_created_from_value(): void
    {
        $status = BookingStatusEnum::from(1);
        $this->assertEquals(BookingStatusEnum::PENDING, $status);

        $status = BookingStatusEnum::from(4);
        $this->assertEquals(BookingStatusEnum::COMPLETED, $status);
    }

    /** @test */
    public function it_returns_null_for_invalid_value_with_try_from(): void
    {
        $status = BookingStatusEnum::tryFrom(99);
        $this->assertNull($status);
    }

    /** @test */
    public function it_has_class_method_for_styling(): void
    {
        $this->assertEquals('warning', BookingStatusEnum::PENDING->class());
        $this->assertEquals('primary', BookingStatusEnum::CONFIRMED->class());
        $this->assertEquals('danger', BookingStatusEnum::CANCELLED->class());
        $this->assertEquals('success', BookingStatusEnum::COMPLETED->class());
        $this->assertEquals('secondary', BookingStatusEnum::NO_SHOW->class());
    }

    /** @test */
    public function it_has_icon_method(): void
    {
        $this->assertEquals('clock', BookingStatusEnum::PENDING->icon());
        $this->assertEquals('check-circle', BookingStatusEnum::CONFIRMED->icon());
        $this->assertEquals('x-circle', BookingStatusEnum::CANCELLED->icon());
        $this->assertEquals('check-double', BookingStatusEnum::COMPLETED->icon());
        $this->assertEquals('user-x', BookingStatusEnum::NO_SHOW->icon());
    }

    /** @test */
    public function it_returns_active_statuses(): void
    {
        $activeStatuses = BookingStatusEnum::activeStatuses();

        $this->assertCount(2, $activeStatuses);
        $this->assertContains(BookingStatusEnum::PENDING, $activeStatuses);
        $this->assertContains(BookingStatusEnum::CONFIRMED, $activeStatuses);
    }
}
