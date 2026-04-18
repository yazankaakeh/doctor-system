<?php

namespace Modules\Doctor\Tests\Unit;

use Modules\Core\App\Enums\ActiveEnum;
use Modules\Core\App\Enums\Gender;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Tests\DoctorTestCase;

class DoctorModelTest extends DoctorTestCase
{
    /** @test */
    public function it_can_create_a_doctor(): void
    {
        $doctor = Doctor::factory()->create([
            'name' => 'Dr. John Doe',
            'email' => 'drjohn@test.com',
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->assertDatabaseHas('doctors', [
            'name' => 'Dr. John Doe',
            'email' => 'drjohn@test.com',
        ]);
    }

    /** @test */
    public function it_casts_gender_to_enum(): void
    {
        $doctor = Doctor::factory()->create([
            'gender' => Gender::MALE,
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->assertInstanceOf(Gender::class, $doctor->gender);
        $this->assertEquals(Gender::MALE, $doctor->gender);
    }

    /** @test */
    public function it_casts_is_active_to_enum(): void
    {
        $doctor = Doctor::factory()->create([
            'is_active' => ActiveEnum::ACTIVE,
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->assertInstanceOf(ActiveEnum::class, $doctor->is_active);
        $this->assertEquals(ActiveEnum::ACTIVE, $doctor->is_active);
    }

    /** @test */
    public function it_hides_password_in_array(): void
    {
        $doctor = Doctor::factory()->create([
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->assertArrayNotHasKey('password', $doctor->toArray());
    }

    /** @test */
    public function it_hides_remember_token_in_array(): void
    {
        // Skip: Requires remember_token column in doctors table
        $this->markTestSkipped('Requires remember_token column in doctors table migration.');
    }

    /** @test */
    public function it_belongs_to_medical_specialty(): void
    {
        $doctor = Doctor::factory()->create([
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->assertEquals($this->medicalSpecialty->id, $doctor->medicalSpecialty->id);
        $this->assertEquals('General Medicine', $doctor->medicalSpecialty->name);
    }

    /** @test */
    public function it_can_have_roles(): void
    {
        $this->assertTrue($this->doctor->hasRole('DOCTOR'));
    }

    /** @test */
    public function it_has_availabilities_relationship(): void
    {
        $this->assertEmpty($this->doctor->availabilities);
    }

    /** @test */
    public function it_has_bookings_relationship(): void
    {
        $this->assertEmpty($this->doctor->bookings);
    }

    /** @test */
    public function it_has_recurring_schedules_relationship(): void
    {
        $this->assertEmpty($this->doctor->recurringSchedules);
    }

    /** @test */
    public function it_has_medical_examinations_relationship(): void
    {
        $this->assertEmpty($this->doctor->medicalExaminations);
    }

    /** @test */
    public function it_can_have_social_accounts(): void
    {
        $doctor = Doctor::factory()->create([
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->assertEmpty($doctor->socialAccounts);
    }

    /** @test */
    public function it_has_bio_field(): void
    {
        $doctor = Doctor::factory()->create([
            'bio' => 'Experienced cardiologist',
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->assertEquals('Experienced cardiologist', $doctor->bio);
    }
}
