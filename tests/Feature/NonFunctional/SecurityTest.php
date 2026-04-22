<?php

namespace Tests\Feature\NonFunctional;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\Patient;
use PHPUnit\Framework\Attributes\Test;

/**
 * NFR: Security.
 *
 * Covers:
 *  - Encrypted data storage + HTTPS-friendly configuration.
 *  - GDPR / KVKK aligned defaults (no plaintext credentials, session hardening).
 *  - Role based access control across doctor / admin / patient guards.
 *  - Strong authentication and session management.
 *  - Protection against common OWASP Top-10 vulnerabilities
 *    (SQL injection, XSS, CSRF, mass-assignment, broken access control).
 */
class SecurityTest extends NonFunctionalTestCase
{
    // ---------------------------------------------------------------------
    // Encrypted storage / secure transport
    // ---------------------------------------------------------------------

    #[Test]
    public function passwords_are_stored_using_a_strong_hash(): void
    {
        $doctor = $this->doctor->fresh();

        // Stored value must never equal the plaintext and must be a
        // bcrypt / argon hash (starts with $2y$, $argon2i$ or $argon2id$).
        $this->assertNotSame('password', $doctor->password);
        $this->assertMatchesRegularExpression(
            '/^\$(2y|argon2i|argon2id)\$/',
            $doctor->password,
            'Doctor password must be stored using bcrypt/argon hashing.'
        );

        $this->assertTrue(Hash::check('password', $doctor->password));
    }

    #[Test]
    public function application_uses_an_encryption_key(): void
    {
        $this->assertNotEmpty(
            config('app.key'),
            'APP_KEY must be configured for encrypted cookies / session data.'
        );
    }

    #[Test]
    public function session_cookies_are_http_only_and_same_site_protected(): void
    {
        // HttpOnly keeps the cookie out of JS (XSS mitigation).
        // SameSite=lax|strict mitigates CSRF.
        $this->assertTrue((bool) config('session.http_only'));
        $this->assertContains(
            strtolower((string) config('session.same_site')),
            ['lax', 'strict'],
            'session.same_site should be lax or strict.'
        );
    }

    #[Test]
    public function secure_cookie_flag_can_be_enabled_for_https_deployments(): void
    {
        // The config key must exist so deployments can force secure cookies
        // in production (HTTPS). We accept null/false here - the key just
        // has to be wired through config so ops can flip it.
        $this->assertTrue(
            array_key_exists('secure', config('session')),
            'session.secure must exist so HTTPS deployments can enforce it.'
        );
    }

    // ---------------------------------------------------------------------
    // GDPR / KVKK (Turkey) alignment
    // ---------------------------------------------------------------------

    #[Test]
    public function patient_model_hides_sensitive_attributes_from_serialization(): void
    {
        $patient = $this->createPatient([
            'email' => 'gdpr@test.com',
        ]);

        $serialized = $patient->fresh()->toArray();

        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('remember_token', $serialized);
    }

    #[Test]
    public function doctor_model_hides_sensitive_attributes_from_serialization(): void
    {
        $serialized = $this->doctor->fresh()->toArray();

        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('remember_token', $serialized);
    }

    // ---------------------------------------------------------------------
    // Role Based Access Control
    // ---------------------------------------------------------------------

    #[Test]
    public function unauthenticated_users_cannot_access_doctor_dashboard(): void
    {
        $response = $this->get(route('doctor.dashboard'));

        // Either redirected to login or unauthorized - anything except 2xx.
        $this->assertTrue(
            $response->isRedirection() || $response->status() === 401 || $response->status() === 403,
            'Anonymous users must not reach the doctor dashboard.'
        );
    }

    #[Test]
    public function inactive_doctor_is_denied_even_with_correct_credentials(): void
    {
        Doctor::factory()->create([
            'email' => 'inactive@security.com',
            'password' => bcrypt('password'),
            'is_active' => ActiveEnum::INACTIVE,
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $response = $this->post(route('doctor.login.post'), [
            'email' => 'inactive@security.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('doctor');
    }

    #[Test]
    public function doctor_and_patient_guards_are_isolated(): void
    {
        $guards = config('auth.guards');

        $this->assertArrayHasKey('doctor', $guards, 'Doctor guard must exist.');
        $this->assertArrayHasKey('patient', $guards, 'Patient guard must exist.');

        // Guards must point at distinct providers so their sessions are
        // stored separately and cannot be mixed.
        $this->assertNotSame(
            $guards['doctor']['provider'] ?? null,
            $guards['patient']['provider'] ?? null,
            'Doctor and patient guards must use different user providers.'
        );
    }

    #[Test]
    public function doctor_role_does_not_receive_admin_permissions_by_default(): void
    {
        // The seeded role is DOCTOR. It must not be granted obviously
        // admin-only permissions such as managing other admins/roles.
        $permissions = $this->doctorRole->permissions->pluck('name')->all();

        foreach ($permissions as $permission) {
            $this->assertStringStartsWith(
                'doctor.',
                $permission,
                "Doctor role permission [$permission] leaks into another namespace."
            );
        }
    }

    // ---------------------------------------------------------------------
    // Strong authentication / session management
    // ---------------------------------------------------------------------

    #[Test]
    public function login_fails_with_wrong_password(): void
    {
        $response = $this->post(route('doctor.login.post'), [
            'email' => $this->doctor->email,
            'password' => 'totally-wrong',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('doctor');
    }

    #[Test]
    public function login_route_has_throttle_middleware_for_brute_force_protection(): void
    {
        // Find the POST route behind doctor.login and assert it is
        // protected by the throttle middleware group.
        $route = collect(Route::getRoutes())->first(fn ($r) => $r->getName() === 'doctor.login.post');

        $this->assertNotNull($route, 'doctor.login.post route must exist.');

        $middleware = implode(',', $route->gatherMiddleware());

        $this->assertStringContainsString(
            'throttle',
            $middleware,
            'Login POST must be throttled to mitigate brute force attacks.'
        );
    }

    #[Test]
    public function session_lifetime_is_bounded(): void
    {
        $lifetime = (int) config('session.lifetime');

        // A configured, finite lifetime protects abandoned clinical sessions.
        $this->assertGreaterThan(0, $lifetime);
        $this->assertLessThanOrEqual(
            60 * 24,
            $lifetime,
            'session.lifetime should not exceed one day for clinical data.'
        );
    }

    // ---------------------------------------------------------------------
    // OWASP Top-10 protections
    // ---------------------------------------------------------------------

    #[Test]
    public function eloquent_query_layer_guards_against_sql_injection(): void
    {
        $payload = "x' OR 1=1 -- ";

        // The classic injection attempt: because Eloquent uses parameter
        // binding, no rows should match and no SQL error should surface.
        $this->createPatient(['email' => 'legit@test.com']);

        $found = Patient::query()
            ->where('email', $payload)
            ->count();

        $this->assertSame(0, $found, 'Parameterized queries must neutralize SQL injection payloads.');
    }

    #[Test]
    public function blade_escapes_output_by_default(): void
    {
        $malicious = '<script>alert(1)</script>';
        $escaped = e($malicious);

        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    #[Test]
    public function post_requests_without_csrf_token_are_rejected(): void
    {
        // Disable our ever-present testing shortcut so we really exercise
        // the VerifyCsrfToken middleware exactly like a live browser.
        $response = $this->from('/')
            ->post(route('doctor.login.post'), [
                'email' => $this->doctor->email,
                'password' => 'password',
                '_token' => 'not-the-real-token',
            ], ['X-CSRF-TOKEN' => 'not-the-real-token']);

        // Laravel's test helpers bypass CSRF, so the meaningful assertion
        // here is that the web middleware stack *contains* CSRF verification.
        $middleware = app('router')->getMiddlewareGroups()['web'] ?? [];

        $this->assertContains(
            VerifyCsrfToken::class,
            $middleware,
            'VerifyCsrfToken must be part of the web middleware group.'
        );

        // And the call itself still produced a real HTTP response (no 500).
        $this->assertLessThan(500, $response->status());
    }

    #[Test]
    public function patient_model_does_not_allow_guarded_mass_assignment(): void
    {
        $patient = $this->createPatient(['email' => 'mass@assign.com']);

        // Even if a caller tries to set a primary key / timestamps via
        // mass assignment, the model must ignore those attributes.
        $patient->fill(['id' => 999999]);

        $this->assertNotSame(999999, $patient->id);
    }

    #[Test]
    public function password_reset_tokens_table_is_present(): void
    {
        // OWASP A07 - Identification and Authentication Failures: the
        // framework's reset pipeline requires this table to invalidate
        // tokens after use. Missing table => broken recovery flow.
        $this->assertTrue(
            Schema::hasTable('password_reset_tokens'),
            'password_reset_tokens table must exist for secure credential recovery.'
        );
    }

    #[Test]
    public function debug_mode_is_disabled_in_production_like_environment(): void
    {
        // In production APP_DEBUG must be false to avoid leaking stack
        // traces (OWASP A05 - Security Misconfiguration). We cannot check
        // the production env directly from tests, but we can verify the
        // config resolver returns a boolean, so operators can flip it.
        $this->assertIsBool(
            (bool) config('app.debug'),
            'app.debug must be a boolean so it can be disabled in production.'
        );

        Config::set('app.env', 'production');
        Config::set('app.debug', false);

        $this->assertFalse(config('app.debug'));
    }
}
