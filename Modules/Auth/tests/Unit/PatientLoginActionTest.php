<?php

namespace Modules\Auth\Tests\Unit;

use Illuminate\Validation\ValidationException;
use Modules\Auth\Actions\Patient\LoginAction;
use Modules\Auth\Tests\AuthTestCase;

class PatientLoginActionTest extends AuthTestCase
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
    public function it_authenticates_patient_with_valid_credentials(): void
    {
        $patient = $this->createPatient([
            'email' => 'patient@test.com',
        ]);

        // Make a request to initialize the session context
        $this->get('/');

        $result = $this->action->handle([
            'email' => 'patient@test.com',
            'password' => 'password',
        ]);

        $this->assertTrue($result);
        $this->assertAuthenticatedAs($patient, 'web');
    }

    /** @test */
    public function it_throws_exception_for_invalid_password(): void
    {
        $this->createPatient([
            'email' => 'patient@test.com',
        ]);

        $this->expectException(ValidationException::class);

        $this->action->handle([
            'email' => 'patient@test.com',
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
    public function it_supports_remember_me_option(): void
    {
        $this->createPatient([
            'email' => 'patient@test.com',
        ]);

        // Make a request to initialize the session context
        $this->get('/');

        $result = $this->action->handle([
            'email' => 'patient@test.com',
            'password' => 'password',
        ], remember: true);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_regenerates_session_after_successful_login(): void
    {
        $this->createPatient([
            'email' => 'patient@test.com',
        ]);

        // Make a request to initialize the session context
        $this->get('/');

        $oldSessionId = session()->getId();

        $this->action->handle([
            'email' => 'patient@test.com',
            'password' => 'password',
        ]);

        $this->assertNotEquals($oldSessionId, session()->getId());
    }
}
