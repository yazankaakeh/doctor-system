<?php

namespace Modules\Booking\Tests\Unit;

use Carbon\Carbon;
use Modules\Booking\Actions\Booking\CreateBookingAction;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Tests\BookingTestCase;

class CreateBookingActionTest extends BookingTestCase
{
    /** @test */
    public function it_creates_booking_for_available_slot(): void
    {
        $availability = $this->createAvailability([
            'date' => Carbon::tomorrow(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'consultation_fee' => 100.00,
        ]);

        $action = app(CreateBookingAction::class);

        $booking = $action->handle([
            'patient_id' => $this->patient->id,
            'doctor_availability_id' => $availability->id,
            'booking_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
            'notes' => 'Test booking',
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals($this->patient->id, $booking->patient_id);
        $this->assertEquals($this->doctor->id, $booking->doctor_id);
        $this->assertEquals(BookingStatusEnum::PENDING, $booking->status);
        $this->assertEquals(100.00, $booking->consultation_fee);
    }

    /** @test */
    public function it_calculates_end_time_based_on_slot_duration(): void
    {
        $availability = $this->createAvailability([
            'date' => Carbon::tomorrow(),
            'slot_duration' => 45,
        ]);

        $action = app(CreateBookingAction::class);

        $booking = $action->handle([
            'patient_id' => $this->patient->id,
            'doctor_availability_id' => $availability->id,
            'booking_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
        ]);

        $this->assertEquals('09:00', Carbon::parse($booking->start_time)->format('H:i'));
        $this->assertEquals('09:45', Carbon::parse($booking->end_time)->format('H:i'));
        $this->assertEquals(45, $booking->duration);
    }

    /** @test */
    public function it_throws_exception_for_unavailable_slot(): void
    {
        $availability = $this->createAvailability([
            'date' => Carbon::tomorrow(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'slot_duration' => 30,
        ]);

        // Book the slot first
        $this->createBooking([
            'doctor_availability_id' => $availability->id,
            'booking_date' => Carbon::tomorrow(),
            'start_time' => '09:00',
            'status' => BookingStatusEnum::CONFIRMED,
        ]);

        $action = app(CreateBookingAction::class);

        $this->expectException(\Exception::class);

        $action->handle([
            'patient_id' => $this->patient->id,
            'doctor_availability_id' => $availability->id,
            'booking_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
        ]);
    }

    /** @test */
    public function it_stores_booking_notes(): void
    {
        $availability = $this->createAvailability();

        $action = app(CreateBookingAction::class);

        $booking = $action->handle([
            'patient_id' => $this->patient->id,
            'doctor_availability_id' => $availability->id,
            'booking_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
            'notes' => 'Patient has mobility issues',
        ]);

        $this->assertEquals('Patient has mobility issues', $booking->notes);
    }

    /** @test */
    public function it_uses_consultation_fee_from_availability(): void
    {
        $availability = $this->createAvailability([
            'consultation_fee' => 250.00,
        ]);

        $action = app(CreateBookingAction::class);

        $booking = $action->handle([
            'patient_id' => $this->patient->id,
            'doctor_availability_id' => $availability->id,
            'booking_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
        ]);

        $this->assertEquals(250.00, $booking->consultation_fee);
    }

    /** @test */
    public function it_assigns_doctor_from_availability(): void
    {
        $availability = $this->createAvailability([
            'doctor_id' => $this->doctor->id,
        ]);

        $action = app(CreateBookingAction::class);

        $booking = $action->handle([
            'patient_id' => $this->patient->id,
            'doctor_availability_id' => $availability->id,
            'booking_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
        ]);

        $this->assertEquals($this->doctor->id, $booking->doctor_id);
    }
}
