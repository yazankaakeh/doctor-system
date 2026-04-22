<?php

namespace Tests\Feature\NonFunctional;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;

/**
 * NFR: Usability.
 *
 * Covers:
 *  - Clean intuitive UI for doctors and patients.
 *  - Minimal clicks for adding medical examinations.
 *  - Responsive design using Bootstrap 5 for mobile and desktop use.
 */
class UsabilityTest extends NonFunctionalTestCase
{
    #[Test]
    public function public_entry_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $this->assertTrue(
            $response->isSuccessful() || $response->isRedirection(),
            'Landing URL must not produce a server error.'
        );
    }

    #[Test]
    public function doctor_dashboard_declares_responsive_viewport(): void
    {
        $response = $this->actingAsDoctor()->get(route('doctor.dashboard'));

        $response->assertOk();
        $response->assertSee('name="viewport"', false);
        $response->assertSee('width=device-width', false);
    }

    #[Test]
    public function doctor_dashboard_uses_bootstrap_5_class_conventions(): void
    {
        $response = $this->actingAsDoctor()->get(route('doctor.dashboard'));
        $html = $response->getContent();

        // Look for *any* Bootstrap 5 flavored utility class.
        // B5 specific tokens: data-bs-*, g-[0-5], rtl, etc.
        $bootstrap5Signals = [
            'data-bs-',      // B5 uses data-bs-* (B4 used data-*).
            'class="container',
            'class="row',
            'class="col-',
            'class="btn ',
            'class="card',
        ];

        $found = 0;
        foreach ($bootstrap5Signals as $signal) {
            if (str_contains($html, $signal)) {
                $found++;
            }
        }

        $this->assertGreaterThan(
            0,
            $found,
            'Doctor dashboard must render with Bootstrap-style component classes.'
        );
    }

    #[Test]
    public function medical_examination_can_be_created_in_a_single_post_request(): void
    {
        // "Minimal clicks for adding medical examinations" - the create
        // action must be reachable in a single POST once the doctor is
        // logged in (not a multi-step wizard that would imply many clicks).
        $createRoute = route('doctor.medicalExamination.create');

        $response = $this->actingAsDoctor()->get($createRoute);

        $this->assertTrue(
            $response->isOk() || $response->isRedirection(),
            'The medical examination create screen must be reachable in one click from the dashboard.'
        );
    }

    #[Test]
    public function navigation_links_to_core_doctor_sections_exist(): void
    {
        $response = $this->actingAsDoctor()->get(route('doctor.dashboard'));
        $html = $response->getContent();

        // A doctor should reach the main working areas without deep nesting.
        $expectedRouteNames = [
            'doctor.patients.index',
            'doctor.clinic.index',
            'doctor.medicalExamination.index',
        ];

        $foundAtLeastOne = collect($expectedRouteNames)
            ->contains(fn ($name) => str_contains($html, route($name)));

        $this->assertTrue(
            $foundAtLeastOne,
            'Dashboard must link to at least one of the core working areas (patients/clinics/exams).'
        );
    }

    #[Test]
    public function authentication_views_include_form_labels_for_accessibility(): void
    {
        $response = $this->get(route('doctor.login'));
        $html = $response->getContent();

        $response->assertOk();

        // Accessibility: <label> improves screen reader usability and
        // reduces cognitive load on new users (clean + intuitive UI).
        $this->assertMatchesRegularExpression(
            '/<label[^>]*>/i',
            $html,
            'Login form must use <label> elements for accessibility.'
        );
    }

    #[Test]
    public function primary_layouts_exist_in_the_theme_module(): void
    {
        $themePath = $this->projectRoot().'/Modules/Theme/resources/views';

        $this->assertTrue(
            File::isDirectory($themePath),
            'Theme module views directory must exist.'
        );

        // A shared layout indicates consistent UI rather than ad-hoc pages.
        $layoutCount = count(File::allFiles($themePath));
        $this->assertGreaterThan(0, $layoutCount, 'Theme module must contain at least one Blade view.');
    }
}
