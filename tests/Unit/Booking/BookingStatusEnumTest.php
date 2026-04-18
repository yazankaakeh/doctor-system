<?php

namespace Tests\Unit\Booking;

use Modules\Booking\Enums\BookingStatusEnum;
use PHPUnit\Framework\TestCase;

class BookingStatusEnumTest extends TestCase
{
    /**
     * @test
     */
    public function it_exposes_all_expected_cases(): void
    {
        $values = array_map(fn (BookingStatusEnum $c) => $c->value, BookingStatusEnum::cases());

        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5], $values);
    }

    /**
     * @test
     */
    public function active_statuses_only_include_pending_and_confirmed(): void
    {
        $active = BookingStatusEnum::activeStatuses();

        $this->assertContains(BookingStatusEnum::PENDING, $active);
        $this->assertContains(BookingStatusEnum::CONFIRMED, $active);
        $this->assertNotContains(BookingStatusEnum::CANCELLED, $active);
        $this->assertNotContains(BookingStatusEnum::COMPLETED, $active);
        $this->assertNotContains(BookingStatusEnum::NO_SHOW, $active);
    }

    /**
     * @test
     *
     * @dataProvider classColorProvider
     */
    public function each_case_maps_to_a_bootstrap_colour_class(BookingStatusEnum $case, string $expected): void
    {
        $this->assertSame($expected, $case->class());
    }

    public static function classColorProvider(): array
    {
        return [
            'pending' => [BookingStatusEnum::PENDING, 'warning'],
            'confirmed' => [BookingStatusEnum::CONFIRMED, 'primary'],
            'cancelled' => [BookingStatusEnum::CANCELLED, 'danger'],
            'completed' => [BookingStatusEnum::COMPLETED, 'success'],
            'no_show' => [BookingStatusEnum::NO_SHOW, 'secondary'],
        ];
    }
}
