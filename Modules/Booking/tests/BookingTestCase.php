<?php

namespace Modules\Booking\Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\DoctorAvailability;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\MedicalSpecialty;
use Modules\Doctor\Models\Patient;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class BookingTestCase extends TestCase
{
    use RefreshDatabase;

    protected Doctor $doctor;

    protected Patient $patient;

    protected Role $doctorRole;

    protected MedicalSpecialty $medicalSpecialty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupPermissions();
        $this->setupMedicalSpecialty();
        $this->setupDoctor();
        $this->setupPatient();
    }

    protected function setupPermissions(): void
    {
        $permissions = [
            'doctor.availability.index' => 'Availability',
            'doctor.availability.store' => 'Availability',
            'doctor.availability.update' => 'Availability',
            'doctor.availability.destroy' => 'Availability',
            'doctor.bookings.index' => 'Bookings',
            'doctor.bookings.show' => 'Bookings',
            'doctor.bookings.complete' => 'Bookings',
            'doctor.bookings.no-show' => 'Bookings',
            'doctor.recurring-schedules.index' => 'Recurring Schedules',
            'doctor.recurring-schedules.store' => 'Recurring Schedules',
            'doctor.recurring-schedules.update' => 'Recurring Schedules',
            'doctor.recurring-schedules.destroy' => 'Recurring Schedules',
            'doctor.recurring-schedules.toggle-status' => 'Recurring Schedules',
            'doctor.recurring-schedules.generate' => 'Recurring Schedules',
            'doctor.schedule-exceptions.index' => 'Schedule Exceptions',
            'doctor.schedule-exceptions.store' => 'Schedule Exceptions',
            'doctor.schedule-exceptions.destroy' => 'Schedule Exceptions',
            'doctor.calendar.availability' => 'Calendar',
            'doctor.calendar.appointments' => 'Calendar',
        ];

        foreach ($permissions as $permission => $section) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'doctor',
                'section' => $section,
            ]);
        }

        $this->doctorRole = Role::create([
            'name' => 'DOCTOR',
            'guard_name' => 'doctor',
        ]);

        $this->doctorRole->givePermissionTo(array_keys($permissions));
    }

    protected function setupMedicalSpecialty(): void
    {
        $this->medicalSpecialty = MedicalSpecialty::factory()->create([
            'name' => 'General Medicine',
        ]);
    }

    protected function setupDoctor(): void
    {
        $this->doctor = Doctor::factory()->create([
            'name' => 'Test Doctor',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'is_active' => ActiveEnum::ACTIVE,
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->doctor->assignRole($this->doctorRole);
    }

    protected function setupPatient(): void
    {
        $this->patient = Patient::factory()->create([
            'name' => 'Test Patient',
            'email' => 'patient@test.com',
            'password' => bcrypt('password'),
            'is_active' => ActiveEnum::ACTIVE,
        ]);
    }

    protected function actingAsDoctor(): self
    {
        return $this->actingAs($this->doctor, 'doctor');
    }

    protected function actingAsPatient(): self
    {
        return $this->actingAs($this->patient, 'web');
    }

    protected function createAvailability(array $attributes = []): DoctorAvailability
    {
        return DoctorAvailability::create(array_merge([
            'doctor_id' => $this->doctor->id,
            'date' => Carbon::tomorrow(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_duration' => 30,
            'consultation_fee' => 100.00,
            'is_active' => true,
            'is_recurring_generated' => false,
        ], $attributes));
    }

    protected function createBooking(array $attributes = []): Booking
    {
        $availability = $attributes['doctor_availability_id'] ?? null;
        if (!$availability) {
            $availability = $this->createAvailability();
        }

        return Booking::create(array_merge([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'doctor_availability_id' => $availability->id ?? $availability,
            'booking_date' => Carbon::tomorrow(),
            'start_time' => '09:00',
            'end_time' => '09:30',
            'duration' => 30,
            'consultation_fee' => 100.00,
            'status' => BookingStatusEnum::PENDING,
        ], $attributes));
    }
}
