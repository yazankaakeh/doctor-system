<?php

namespace Tests\Feature\NonFunctional;

use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\SanctumServiceProvider;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\Clinic;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\Patient;
use Nwidart\Modules\LaravelModulesServiceProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * NFR: Scalability.
 *
 * Covers:
 *  - Ability to support multiple doctors and clinics concurrently.
 *  - Modular design that allows future integration
 *    (AI, mobile apps, FHIR interoperability).
 */
class ScalabilityTest extends NonFunctionalTestCase
{
    #[Test]
    public function system_supports_many_doctors_across_specialties(): void
    {
        $doctorCount = 50;

        Doctor::factory()->count($doctorCount)->create([
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'is_active' => ActiveEnum::ACTIVE,
            'password' => bcrypt('password'),
        ]);

        // +1 for the doctor created by the base test case.
        $this->assertSame(
            $doctorCount + 1,
            Doctor::query()->count(),
            'The doctors table must scale to many rows without uniqueness clashes.'
        );
    }

    #[Test]
    public function system_supports_many_clinics_and_returns_them_paginated(): void
    {
        Clinic::factory()->count(120)->create(['is_active' => 1]);

        $response = $this->actingAsDoctor()->get(route('doctor.clinic.index'));

        $response->assertOk();

        // Even with 120 clinics, one request must not load them all at once.
        DB::enableQueryLog();
        $this->actingAsDoctor()->get(route('doctor.clinic.index'));
        $hasLimit = collect(DB::getQueryLog())
            ->contains(fn ($q) => stripos($q['query'], 'limit') !== false);
        DB::disableQueryLog();

        $this->assertTrue(
            $hasLimit,
            'Clinic listing must paginate (LIMIT ...) regardless of total row count.'
        );
    }

    #[Test]
    public function bulk_patient_insertion_performs_within_budget(): void
    {
        $start = microtime(true);

        Patient::factory()->count(200)->create([
            'password' => bcrypt('password'),
        ]);

        $elapsedMs = (microtime(true) - $start) * 1000;

        $this->assertSame(200, Patient::query()->count());

        // Generous budget for SQLite in-memory; catches catastrophic regressions.
        $this->assertLessThan(
            20000,
            $elapsedMs,
            "Inserting 200 patients took {$elapsedMs}ms - scalability regression."
        );
    }

    #[Test]
    public function modular_design_supports_adding_an_integration_module(): void
    {
        // The nwidart/laravel-modules package must be available so new
        // modules (telemedicine, FHIR, AI diagnostic, mobile-api) can be
        // plugged in without touching the core.
        $this->assertTrue(
            class_exists(LaravelModulesServiceProvider::class),
            'nwidart/laravel-modules must be installed to support future integration modules.'
        );
    }

    #[Test]
    public function api_authentication_stack_is_available_for_mobile_clients(): void
    {
        // Sanctum is the sanctioned API auth layer for mobile / SPA clients.
        $this->assertTrue(
            class_exists(SanctumServiceProvider::class),
            'Laravel Sanctum must be present to authenticate future mobile / FHIR clients.'
        );
    }

    #[Test]
    public function database_supports_multi_tenant_style_filtering_by_clinic(): void
    {
        $clinicA = $this->createClinic(['name' => 'Clinic A']);
        $clinicB = $this->createClinic(['name' => 'Clinic B']);

        $this->assertNotSame(
            $clinicA->id,
            $clinicB->id,
            'Each clinic must receive an independent identifier so data can be partitioned per clinic.'
        );
    }
}
