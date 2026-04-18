<?php

namespace Modules\Booking\Tests\Unit;

use Carbon\Carbon;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\DoctorAvailability;
use Modules\Booking\Tests\BookingTestCase;
use Modules\Doctor\Models\Doctor;

class DoctorAvailabilityModelTest extends BookingTestCase
{
    /** @test */
    public function it_can_create_availability(): void
    {
        $availability = DoctorAvailability::create([
            'doctor_id' => $this->doctor->id,
            'date' => Carbon::tomorrow(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'consultation_fee' => 100.00,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('doctor_availabilities', [
            'doctor_id' => $this->doctor->id,
            'slot_duration' => 30,
        ]);
    }

    /** @test */
    public function it_casts_date_to_carbon(): void
    {
        $availability = $this->createAvailability([
            'date' => '2025-12-25',
        ]);

        $this->assertInstanceOf(Carbon::class, $availability->date);
    }

    /** @test */
    public function it_casts_is_active_to_boolean(): void
    {
        $availability = $this->createAvailability([
            'is_active' => true,
        ]);

        $this->assertTrue($availability->is_active);
        $this->assertIsBool($availability->is_active);
    }

    /** @test */
    public function it_belongs_to_doctor(): void
    {
        $availability = $this->createAvailability();

        $this->assertEquals($this->doctor->id, $availability->doctor->id);
    }

    /** @test */
    public function it_has_many_bookings(): void
    {
        $availability = $this->createAvailability();
        $this->createBooking([
            'doctor_availability_id' => $availability->id,
            'start_time' => '09:00',
        ]);
        $this->createBooking([
            'doctor_availability_id' => $availability->id,
            'start_time' => '09:30',
        ]);

        $this->assertCount(2, $availability->bookings);
    }

    /** @test */
    public function it_generates_slots_correctly(): void
    {
        $availability = $this->createAvailability([
            'start_time' => '09:00',
            'end_time' => '11:00',
            'slot_duration' => 30,
        ]);

        $slots = $availability->generateSlots();

        $this->assertCount(4, $slots); // 09:00, 09:30, 10:00, 10:30
        $this->assertEquals('09:00', $slots[0]['start']);
        $this->assertEquals('09:30', $slots[0]['end']);
        $this->assertEquals('10:30', $slots[3]['start']);
        $this->assertEquals('11:00', $slots[3]['end']);
    }

    /** @test */
    public function it_generates_slots_with_different_duration(): void
    {
        $availability = $this->createAvailability([
            'start_time' => '09:00',
            'end_time' => '10:00',
            'slot_duration' => 15,
        ]);

        $slots = $availability->generateSlots();

        $this->assertCount(4, $slots); // 09:00, 09:15, 09:30, 09:45
    }

    /** @test */
    public function it_returns_available_slots_excluding_booked(): void
    {
        $availability = $this->createAvailability([
            'start_time' => '09:00',
            'end_time' => '10:00',
            'slot_duration' => 30,
        ]);

        // Book the 09:00 slot
        $this->createBooking([
            'doctor_availability_id' => $availability->id,
            'start_time' => '09:00',
            'status' => BookingStatusEnum::CONFIRMED,
        ]);

        $availableSlots = $availability->getAvailableSlots();

        $this->assertCount(1, $availableSlots); // Only 09:30 should be available
    }

    /** @test */
    public function it_can_scope_active_availabilities(): void
    {
        $this->createAvailability(['is_active' => true]);
        $this->createAvailability(['is_active' => false]);

        $activeAvailabilities = DoctorAvailability::active()->get();

        $this->assertCount(1, $activeAvailabilities);
    }

    /** @test */
    public function it_can_scope_upcoming_availabilities(): void
    {
        $this->createAvailability(['date' => Carbon::tomorrow()]);
        $this->createAvailability(['date' => Carbon::yesterday()]);

        $upcomingAvailabilities = DoctorAvailability::upcoming()->get();

        $this->assertCount(1, $upcomingAvailabilities);
    }

    /** @test */
    public function it_can_scope_for_doctor(): void
    {
        $otherDoctor = Doctor::factory()->create([
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->createAvailability(['doctor_id' => $this->doctor->id]);
        DoctorAvailability::create([
            'doctor_id' => $otherDoctor->id,
            'date' => Carbon::tomorrow(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'consultation_fee' => 100.00,
            'is_active' => true,
        ]);

        $doctorAvailabilities = DoctorAvailability::forDoctor($this->doctor->id)->get();

        $this->assertCount(1, $doctorAvailabilities);
    }

    /** @test */
    public function it_can_scope_for_date_range(): void
    {
        $this->createAvailability(['date' => Carbon::parse('2025-01-15')]);
        $this->createAvailability(['date' => Carbon::parse('2025-01-20')]);
        $this->createAvailability(['date' => Carbon::parse('2025-01-25')]);

        $rangeAvailabilities = DoctorAvailability::forDateRange('2025-01-14', '2025-01-21')->get();

        $this->assertCount(2, $rangeAvailabilities);
    }

    /** @test */
    public function it_can_scope_recurring_generated(): void
    {
        $this->createAvailability(['is_recurring_generated' => true]);
        $this->createAvailability(['is_recurring_generated' => false]);

        $recurringGenerated = DoctorAvailability::recurringGenerated()->get();

        $this->assertCount(1, $recurringGenerated);
    }

    /** @test */
    public function it_can_scope_manually_created(): void
    {
        $this->createAvailability(['is_recurring_generated' => true]);
        $this->createAvailability(['is_recurring_generated' => false]);

        $manuallyCreated = DoctorAvailability::manuallyCreated()->get();

        $this->assertCount(1, $manuallyCreated);
    }

    /** @test */
    public function it_checks_if_has_active_bookings(): void
    {
        $availability = $this->createAvailability();

        $this->assertFalse($availability->hasActiveBookings());

        $this->createBooking([
            'doctor_availability_id' => $availability->id,
            'status' => BookingStatusEnum::CONFIRMED,
        ]);

        $this->assertTrue($availability->fresh()->hasActiveBookings());
    }

    /** @test */
    public function cancelled_bookings_do_not_count_as_active(): void
    {
        $availability = $this->createAvailability();

        $this->createBooking([
            'doctor_availability_id' => $availability->id,
            'status' => BookingStatusEnum::CANCELLED,
        ]);

        $this->assertFalse($availability->fresh()->hasActiveBookings());
    }
}
