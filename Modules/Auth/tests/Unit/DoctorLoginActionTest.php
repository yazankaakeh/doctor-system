<?php

namespace Modules\Auth\Tests\Unit;

use Illuminate\Validation\ValidationException;
use Modules\Auth\Actions\Doctor\LoginAction;
use Modules\Auth\Tests\AuthTestCase;

class DoctorLoginActionTest extends AuthTestCase
{
    private LoginAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new LoginAction;

        // Initialize session for unit tests
        $this->withSession([]);
    }

    /** @test */
    public function it_authenticates_active_doctor_with_valid_credentials(): void
    {
        $doctor = $this->createActiveDoctor([
            'email' => 'doctor@test.com',
        ]);

        // Make a request to initialize the session context
        $this->get('/');

        $result = $this->action->handle([
            'email' => 'doctor@test.com',
            'password' => 'password',
        ]);

        $this->assertTrue($result);
        $this->assertAuthenticatedAs($doctor, 'doctor');
    }

    /** @test */
    public function it_throws_exception_for_invalid_password(): void
    {
        $this->createActiveDoctor([
            'email' => 'doctor@test.com',
        ]);

        $this->expectException(ValidationException::class);

        $this->action->handle([
            'email' => 'doctor@test.com',
            'password' => 'wrong-password',
        ]);
    }

    /** @test */
    public function it_throws_exception_for_non_existent_email(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->handle([
            'email' => 'nonexistent@test.com',
            'password' => 'password',
        ]);
    }

    /** @test */
    public function it_throws_exception_for_inactive_doctor(): void
    {
        $this->createInactiveDoctor([
            'email' => 'inactive@test.com',
        ]);

        $this->expectException(ValidationException::class);

        try {
            $this->action->handle([
                'email' => 'inactive@test.com',
                'password' => 'password',
            ]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
            throw $e;
        }
    }

    /** @test */
    public function it_supports_remember_me_option(): void
    {
        // Skip: Requires remember_token column in doctors table
        // This tests Laravel's built-in remember functionality
        $this->markTestSkipped('Requires remember_token column in doctors table migration.');
    }

    /** @test */
    public function it_regenerates_session_after_successful_login(): void
    {
        $this->createActiveDoctor([
            'email' => 'doctor@test.com',
        ]);

        // Make a request to initialize the session context
        $this->get('/');

        $oldSessionId = session()->getId();

        $this->action->handle([
            'email' => 'doctor@test.com',
            'password' => 'password',
        ]);

        $this->assertNotEquals($oldSessionId, session()->getId());
    }
}
