<?php

namespace Modules\Auth\Tests\Unit;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Actions\Patient\RegisterAction;
use Modules\Auth\Tests\AuthTestCase;
use Modules\Doctor\Models\Patient;

class PatientRegisterActionTest extends AuthTestCase
{
    private RegisterAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RegisterAction;
    }

    /** @test */
    public function it_creates_a_new_patient(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'John Patient',
            'email' => 'johnpatient@test.com',
            'phone' => '1234567890',
            'password' => 'securepassword',
        ];

        $patient = $this->action->handle($data);

        $this->assertInstanceOf(Patient::class, $patient);
        $this->assertEquals('John Patient', $patient->name);
        $this->assertEquals('johnpatient@test.com', $patient->email);
        $this->assertDatabaseHas('patients', [
            'email' => 'johnpatient@test.com',
            'name' => 'John Patient',
        ]);
    }

    /** @test */
    public function it_hashes_the_password(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Test Patient',
            'email' => 'test@test.com',
            'phone' => '1234567890',
            'password' => 'plainpassword',
        ];

        $patient = $this->action->handle($data);

        $this->assertTrue(Hash::check('plainpassword', $patient->password));
    }

    /** @test */
    public function it_sets_new_patient_as_active(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Active Patient',
            'email' => 'active@test.com',
            'phone' => '1234567890',
            'password' => 'password',
        ];

        $patient = $this->action->handle($data);

        $this->assertEquals(1, $patient->is_active->value);
    }

    /** @test */
    public function it_fires_registered_event(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Event Patient',
            'email' => 'event@test.com',
            'phone' => '1234567890',
            'password' => 'password',
        ];

        $this->action->handle($data);

        Event::assertDispatched(Registered::class, function ($event) {
            return $event->user->email === 'event@test.com';
        });
    }

    /** @test */
    public function it_logs_in_patient_after_registration(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Login Patient',
            'email' => 'login@test.com',
            'phone' => '1234567890',
            'password' => 'password',
        ];

        $patient = $this->action->handle($data);

        $this->assertTrue(Auth::guard('web')->check());
        $this->assertEquals($patient->id, Auth::guard('web')->id());
    }

    /** @test */
    public function it_stores_optional_fields(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Complete Patient',
            'email' => 'complete@test.com',
            'phone' => '1234567890',
            'password' => 'password',
            'age' => 30,
            'gender' => 1, // Integer value for enum
            'work' => 'Engineer',
        ];

        $patient = $this->action->handle($data);

        $this->assertEquals('Engineer', $patient->work);
    }
}
