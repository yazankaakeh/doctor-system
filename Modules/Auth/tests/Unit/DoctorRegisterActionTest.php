<?php

namespace Modules\Auth\Tests\Unit;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Actions\Doctor\RegisterAction;
use Modules\Auth\Tests\AuthTestCase;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\Doctor;

class DoctorRegisterActionTest extends AuthTestCase
{
    private RegisterAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RegisterAction();
    }

    /** @test */
    public function it_creates_a_new_doctor(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Dr. John Doe',
            'email' => 'johndoe@test.com',
            'phone' => '1234567890',
            'password' => 'securepassword',
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'gender' => 1,
            'age' => 35,
        ];

        $doctor = $this->action->handle($data);

        $this->assertInstanceOf(Doctor::class, $doctor);
        $this->assertEquals('Dr. John Doe', $doctor->name);
        $this->assertEquals('johndoe@test.com', $doctor->email);
        $this->assertDatabaseHas('doctors', [
            'email' => 'johndoe@test.com',
            'name' => 'Dr. John Doe',
        ]);
    }

    /** @test */
    public function it_hashes_the_password(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Dr. Test',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'password' => 'plainpassword',
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'gender' => 1,
            'age' => 35,
        ];

        $doctor = $this->action->handle($data);

        $this->assertTrue(Hash::check('plainpassword', $doctor->password));
    }

    /** @test */
    public function it_sets_new_doctor_as_inactive_pending_approval(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Dr. Pending',
            'email' => 'pending@test.com',
            'phone' => '1234567890',
            'password' => 'password',
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'gender' => 1,
            'age' => 35,
        ];

        $doctor = $this->action->handle($data);

        $this->assertEquals(ActiveEnum::INACTIVE, $doctor->is_active);
    }

    /** @test */
    public function it_fires_registered_event(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Dr. Event',
            'email' => 'event@test.com',
            'phone' => '1234567890',
            'password' => 'password',
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'gender' => 1,
            'age' => 35,
        ];

        $this->action->handle($data);

        Event::assertDispatched(Registered::class, function ($event) {
            return $event->user->email === 'event@test.com';
        });
    }

    /** @test */
    public function it_stores_optional_fields(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Dr. Complete',
            'email' => 'complete@test.com',
            'phone' => '1234567890',
            'password' => 'password',
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'gender' => 1,
            'age' => 35,
        ];

        $doctor = $this->action->handle($data);

        $this->assertEquals(35, $doctor->age);
    }

    /** @test */
    public function it_associates_doctor_with_medical_specialty(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Dr. Specialist',
            'email' => 'specialist@test.com',
            'phone' => '1234567890',
            'password' => 'password',
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'gender' => 1,
            'age' => 40,
        ];

        $doctor = $this->action->handle($data);

        $this->assertEquals($this->medicalSpecialty->id, $doctor->medical_specialty_id);
    }
}
