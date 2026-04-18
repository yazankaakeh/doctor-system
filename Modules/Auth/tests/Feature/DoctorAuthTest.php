<?php

namespace Modules\Auth\Tests\Feature;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Tests\AuthTestCase;

class DoctorAuthTest extends AuthTestCase
{
    /** @test */
    public function it_displays_doctor_login_page(): void
    {
        $response = $this->get(route('doctor.login'));

        $response->assertOk();
        $response->assertViewIs('auth::doctor.login');
    }

    /** @test */
    public function active_doctor_can_login(): void
    {
        $doctor = $this->createActiveDoctor([
            'email' => 'active@doctor.com',
        ]);

        $response = $this->post(route('doctor.login.post'), [
            'email' => 'active@doctor.com',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($doctor, 'doctor');
    }

    /** @test */
    public function inactive_doctor_cannot_login(): void
    {
        $this->createInactiveDoctor([
            'email' => 'inactive@doctor.com',
        ]);

        $response = $this->post(route('doctor.login.post'), [
            'email' => 'inactive@doctor.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('doctor');
    }

    /** @test */
    public function login_fails_with_invalid_credentials(): void
    {
        $this->createActiveDoctor([
            'email' => 'doctor@test.com',
        ]);

        $response = $this->post(route('doctor.login.post'), [
            'email' => 'doctor@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('doctor');
    }

    /** @test */
    public function login_requires_email(): void
    {
        $response = $this->post(route('doctor.login.post'), [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function login_requires_password(): void
    {
        $response = $this->post(route('doctor.login.post'), [
            'email' => 'doctor@test.com',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function authenticated_doctor_can_logout(): void
    {
        $doctor = $this->createActiveDoctor();

        $response = $this->actingAs($doctor, 'doctor')
            ->post(route('doctor.logout'));

        $response->assertRedirect();
        $this->assertGuest('doctor');
    }

    /** @test */
    public function it_displays_doctor_registration_page(): void
    {
        $response = $this->get(route('doctor.register'));

        $response->assertOk();
        $response->assertViewIs('auth::doctor.register');
    }

    /** @test */
    public function doctor_can_register(): void
    {
        Event::fake([Registered::class]);

        $response = $this->post(route('doctor.register.post'), [
            'name' => 'New Doctor',
            'email' => 'newdoctor@test.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'gender' => 1, // Male
            'age' => 35,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('doctors', [
            'email' => 'newdoctor@test.com',
            'name' => 'New Doctor',
        ]);

        Event::assertDispatched(Registered::class);
    }

    /** @test */
    public function doctor_registration_requires_unique_email(): void
    {
        $this->createActiveDoctor([
            'email' => 'existing@test.com',
        ]);

        $response = $this->post(route('doctor.register.post'), [
            'name' => 'New Doctor',
            'email' => 'existing@test.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'gender' => 1,
            'age' => 35,
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function doctor_registration_requires_medical_specialty(): void
    {
        $response = $this->post(route('doctor.register.post'), [
            'name' => 'New Doctor',
            'email' => 'newdoctor@test.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'gender' => 1,
            'age' => 35,
        ]);

        $response->assertSessionHasErrors('medical_specialty_id');
    }

    /** @test */
    public function it_displays_forgot_password_page(): void
    {
        $response = $this->get(route('doctor.password.request'));

        $response->assertOk();
        $response->assertViewIs('auth::doctor.forgot-password');
    }

    /** @test */
    public function authenticated_doctor_is_redirected_from_login_page(): void
    {
        $doctor = $this->createActiveDoctor();

        $response = $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.login'));

        $response->assertRedirect();
    }
}
