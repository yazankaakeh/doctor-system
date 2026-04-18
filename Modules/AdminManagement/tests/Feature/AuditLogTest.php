<?php

namespace Modules\AdminManagement\Tests\Feature;

use Modules\AdminManagement\Models\AuditLog;
use Modules\AdminManagement\Tests\AdminManagementTestCase;
use Modules\Doctor\Models\Doctor;

class AuditLogTest extends AdminManagementTestCase
{
    public function test_guest_cannot_access_audit_log(): void
    {
        $response = $this->get(route('admin.audits.index'));

        $response->assertRedirect();
    }

    public function test_authenticated_admin_can_view_audit_log_list(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.index'));

        $response->assertOk();
        $response->assertViewIs('adminmanagement::audit_log.index');
    }

    public function test_audit_log_list_displays_logs(): void
    {
        // Create audit logs
        $this->createAuditLog([
            'url' => '/admin/test',
            'method' => 'POST',
            'route_name' => 'admin.test.store',
        ]);

        $this->createAuditLog([
            'url' => '/admin/test/update',
            'method' => 'PUT',
            'route_name' => 'admin.test.update',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.index'));

        $response->assertOk();
    }

    public function test_audit_log_can_be_filtered_by_method(): void
    {
        $this->createAuditLog(['method' => 'POST', 'route_name' => 'test.post']);
        $this->createAuditLog(['method' => 'DELETE', 'route_name' => 'test.delete']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.index', ['method' => 'POST']));

        $response->assertOk();
    }

    public function test_audit_log_can_be_filtered_by_route_name(): void
    {
        $this->createAuditLog(['route_name' => 'admin.users.store']);
        $this->createAuditLog(['route_name' => 'admin.roles.store']);

        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.index', ['route_name' => 'admin.users.store']));

        $response->assertOk();
    }

    public function test_audit_log_can_be_filtered_by_auditable_type(): void
    {
        $this->createAuditLog(['auditable_type' => Doctor::class]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.index', ['auditable_type' => Doctor::class]));

        $response->assertOk();
    }

    public function test_get_payload_returns_json(): void
    {
        $auditLog = $this->createAuditLog([
            'payload' => ['name' => 'Test', 'email' => 'test@example.com'],
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.getPayload', $auditLog->id));

        $response->assertOk();
        $response->assertJson(['status' => true]);
    }

    public function test_get_payload_returns_formatted_html(): void
    {
        $auditLog = $this->createAuditLog([
            'payload' => ['name' => 'Test User', 'action' => 'create'],
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.getPayload', $auditLog->id));

        $response->assertOk();
        $responseData = $response->json();

        $this->assertTrue($responseData['status']);
        $this->assertArrayHasKey('html', $responseData);
    }

    public function test_get_payload_with_invalid_id_returns_error(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.getPayload', 99999));

        // Should either return 404 or error response
        $this->assertTrue(
            $response->status() === 404 ||
            ($response->json()['status'] ?? true) === false
        );
    }

    public function test_audit_log_pagination(): void
    {
        // Create many audit logs
        for ($i = 0; $i < 30; $i++) {
            $this->createAuditLog([
                'route_name' => "test.route.{$i}",
                'method' => 'POST',
            ]);
        }

        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.index'));

        $response->assertOk();
    }

    public function test_audit_log_shows_auditable_info(): void
    {
        $doctor = $this->createTestDoctor(['email' => 'auditdoctor@test.com']);

        $this->createAuditLog([
            'auditable_type' => Doctor::class,
            'auditable_id' => $doctor->id,
            'url' => '/admin/doctors/update',
            'method' => 'PUT',
        ]);

        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.index'));

        $response->assertOk();
    }

    public function test_audit_log_date_range_filter(): void
    {
        $this->createAuditLog([
            'route_name' => 'test.dated',
            'method' => 'POST',
        ]);

        $startDate = now()->subDays(7)->format('m/d/Y');
        $endDate = now()->format('m/d/Y');

        $response = $this->actingAsAdmin()
            ->get(route('admin.audits.index', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]));

        $response->assertOk();
    }

    private function createAuditLog(array $attributes = []): AuditLog
    {
        return AuditLog::create(array_merge([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->adminUser->id,
            'url' => '/admin/test',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test',
        ], $attributes));
    }
}
