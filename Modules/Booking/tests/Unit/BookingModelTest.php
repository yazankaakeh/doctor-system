<?php

namespace Modules\Booking\Tests\Unit;

use Carbon\Carbon;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\DoctorAvailability;
use Modules\Booking\Tests\BookingTestCase;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\Patient;

class BookingModelTest extends BookingTestCase
{
    /** @test */
    public function it_can_create_a_booking(): void
    {
        $availability = $this->createAvailability();

        $booking = Booking::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'doctor_availability_id' => $availability->id,
            'booking_date' => Carbon::tomorrow(),
            'start_time' => '09:00',
            'end_time' => '09:30',
            'duration' => 30,
            'consultation_fee' => 100.00,
            'status' => BookingStatusEnum::PENDING,
        ]);

        $this->assertDatabaseHas('bookings', [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
        ]);
    }

    /** @test */
    public function it_casts_status_to_enum(): void
    {
        $booking = $this->createBooking([
            'status' => BookingStatusEnum::PENDING,
        ]);

        $this->assertInstanceOf(BookingStatusEnum::class, $booking->status);
        $this->assertEquals(BookingStatusEnum::PENDING, $booking->status);
    }

    /** @test */
    public function it_casts_booking_date_to_date(): void
    {
        $booking = $this->createBooking([
            'booking_date' => '2025-12-25',
        ]);

        $this->assertInstanceOf(Carbon::class, $booking->booking_date);
    }

    /** @test */
    public function it_belongs_to_patient(): void
    {
        $booking = $this->createBooking();

        $this->assertEquals($this->patient->id, $booking->patient->id);
        $this->assertEquals($this->patient->name, $booking->patient->name);
    }

    /** @test */
    public function it_belongs_to_doctor(): void
    {
        $booking = $this->createBooking();

        $this->assertEquals($this->doctor->id, $booking->doctor->id);
        $this->assertEquals($this->doctor->name, $booking->doctor->name);
    }

    /** @test */
    public function it_belongs_to_doctor_availability(): void
    {
        $availability = $this->createAvailability();
        $booking = $this->createBooking([
            'doctor_availability_id' => $availability->id,
        ]);

        $this->assertEquals($availability->id, $booking->doctorAvailability->id);
    }

    /** @test */
    public function it_can_check_if_pending(): void
    {
        $booking = $this->createBooking([
            'status' => BookingStatusEnum::PENDING,
        ]);

        $this->assertTrue($booking->isPending());
        $this->assertFalse($booking->isConfirmed());
    }

    /** @test */
    public function it_can_check_if_confirmed(): void
    {
        $booking = $this->createBooking([
            'status' => BookingStatusEnum::CONFIRMED,
        ]);

        $this->assertTrue($booking->isConfirmed());
        $this->assertFalse($booking->isPending());
    }

    /** @test */
    public function it_can_check_if_cancelled(): void
    {
        $booking = $this->createBooking([
            'status' => BookingStatusEnum::CANCELLED,
        ]);

        $this->assertTrue($booking->isCancelled());
        $this->assertFalse($booking->canBeCancelled());
    }

    /** @test */
    public function it_can_check_if_completed(): void
    {
        $booking = $this->createBooking([
            'status' => BookingStatusEnum::COMPLETED,
        ]);

        $this->assertTrue($booking->isCompleted());
    }

    /** @test */
    public function pending_booking_can_be_cancelled(): void
    {
        $booking = $this->createBooking([
            'status' => BookingStatusEnum::PENDING,
        ]);

        $this->assertTrue($booking->canBeCancelled());
    }

    /** @test */
    public function confirmed_booking_can_be_cancelled(): void
    {
        $booking = $this->createBooking([
            'status' => BookingStatusEnum::CONFIRMED,
        ]);

        $this->assertTrue($booking->canBeCancelled());
    }

    /** @test */
    public function completed_booking_cannot_be_cancelled(): void
    {
        $booking = $this->createBooking([
            'status' => BookingStatusEnum::COMPLETED,
        ]);

        $this->assertFalse($booking->canBeCancelled());
    }

    /** @test */
    public function it_can_scope_pending_bookings(): void
    {
        $this->createBooking(['status' => BookingStatusEnum::PENDING, 'start_time' => '09:00', 'end_time' => '09:30']);
        $this->createBooking(['status' => BookingStatusEnum::CONFIRMED, 'start_time' => '10:00', 'end_time' => '10:30']);
        $this->createBooking(['status' => BookingStatusEnum::CANCELLED, 'start_time' => '11:00', 'end_time' => '11:30']);

        $pendingBookings = Booking::pending()->get();

        $this->assertCount(1, $pendingBookings);
    }

    /** @test */
    public function it_can_scope_confirmed_bookings(): void
    {
        $this->createBooking(['status' => BookingStatusEnum::PENDING, 'start_time' => '09:00', 'end_time' => '09:30']);
        $this->createBooking(['status' => BookingStatusEnum::CONFIRMED, 'start_time' => '10:00', 'end_time' => '10:30']);

        $confirmedBookings = Booking::confirmed()->get();

        $this->assertCount(1, $confirmedBookings);
    }

    /** @test */
    public function it_can_scope_active_bookings(): void
    {
        $this->createBooking(['status' => BookingStatusEnum::PENDING, 'start_time' => '09:00', 'end_time' => '09:30']);
        $this->createBooking(['status' => BookingStatusEnum::CONFIRMED, 'start_time' => '10:00', 'end_time' => '10:30']);
        $this->createBooking(['status' => BookingStatusEnum::CANCELLED, 'start_time' => '11:00', 'end_time' => '11:30']);

        $activeBookings = Booking::active()->get();

        $this->assertCount(2, $activeBookings);
    }

    /** @test */
    public function it_can_scope_upcoming_bookings(): void
    {
        $this->createBooking(['booking_date' => Carbon::tomorrow(), 'start_time' => '09:00', 'end_time' => '09:30']);
        $this->createBooking(['booking_date' => Carbon::yesterday(), 'start_time' => '10:00', 'end_time' => '10:30']);

        $upcomingBookings = Booking::upcoming()->get();

        $this->assertCount(1, $upcomingBookings);
    }

    /** @test */
    public function it_can_scope_for_patient(): void
    {
        $otherPatient = Patient::factory()->create();

        $this->createBooking(['patient_id' => $this->patient->id, 'start_time' => '09:00', 'end_time' => '09:30']);
        $this->createBooking(['patient_id' => $otherPatient->id, 'start_time' => '10:00', 'end_time' => '10:30']);

        $patientBookings = Booking::forPatient($this->patient->id)->get();

        $this->assertCount(1, $patientBookings);
    }

    /** @test */
    public function it_can_scope_for_doctor(): void
    {
        $otherDoctor = Doctor::factory()->create([
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->createBooking(['doctor_id' => $this->doctor->id]);

        $availability = DoctorAvailability::create([
            'doctor_id' => $otherDoctor->id,
            'date' => Carbon::tomorrow(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'consultation_fee' => 100.00,
            'is_active' => true,
        ]);

        Booking::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $otherDoctor->id,
            'doctor_availability_id' => $availability->id,
            'booking_date' => Carbon::tomorrow(),
            'start_time' => '10:00',
            'end_time' => '10:30',
            'duration' => 30,
            'consultation_fee' => 100.00,
            'status' => BookingStatusEnum::PENDING,
        ]);

        $doctorBookings = Booking::forDoctor($this->doctor->id)->get();

        $this->assertCount(1, $doctorBookings);
    }

    /** @test */
    public function it_stores_cancellation_details(): void
    {
        $booking = $this->createBooking([
            'status' => BookingStatusEnum::CANCELLED,
            'cancellation_reason' => 'Patient requested cancellation',
            'cancelled_at' => Carbon::now(),
        ]);

        $this->assertEquals('Patient requested cancellation', $booking->cancellation_reason);
        $this->assertNotNull($booking->cancelled_at);
    }

    /** @test */
    public function it_can_have_meeting_link(): void
    {
        $booking = $this->createBooking([
            'meeting_link' => 'https://zoom.us/j/123456789',
        ]);

        $this->assertEquals('https://zoom.us/j/123456789', $booking->meeting_link);
    }

    /** @test */
    public function it_can_have_notes(): void
    {
        $booking = $this->createBooking([
            'notes' => 'Patient prefers morning appointments',
        ]);

        $this->assertEquals('Patient prefers morning appointments', $booking->notes);
    }
}
