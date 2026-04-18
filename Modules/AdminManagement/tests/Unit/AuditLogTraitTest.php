<?php

namespace Modules\AdminManagement\Tests\Unit;

use Modules\AdminManagement\Models\AuditLog;
use Modules\AdminManagement\Tests\AdminManagementTestCase;
use Modules\Doctor\Models\Doctor;

class AuditLogTraitTest extends AdminManagementTestCase
{
    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->doctor = $this->createTestDoctor(['email' => 'traitdoctor@test.com']);
    }

    public function test_model_has_audit_logs_relationship(): void
    {
        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/test',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test',
        ]);

        $this->assertCount(1, $this->doctor->auditLogs);
    }

    public function test_create_audit_log_method(): void
    {
        $this->doctor->createAuditLog([
            'url' => '/admin/doctor/update',
            'method' => 'PUT',
            'payload' => ['name' => 'Updated Name'],
            'ip' => '192.168.1.1',
            'route_name' => 'admin.doctor.update',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/doctor/update',
            'method' => 'PUT',
        ]);
    }

    public function test_latest_audit_log_returns_most_recent(): void
    {
        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/first',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.first',
            'created_at' => now()->subHour(),
        ]);

        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/second',
            'method' => 'PUT',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.second',
            'created_at' => now(),
        ]);

        $latest = $this->doctor->latestAuditLog();

        $this->assertEquals('admin.second', $latest->route_name);
    }

    public function test_audit_logs_for_route_filters_correctly(): void
    {
        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/store',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.store',
        ]);

        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/update',
            'method' => 'PUT',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.update',
        ]);

        $storeOnly = $this->doctor->auditLogsForRoute('admin.store');

        $this->assertCount(1, $storeOnly->get());
        $this->assertEquals('admin.store', $storeOnly->first()->route_name);
    }

    public function test_audit_logs_for_method_filters_correctly(): void
    {
        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/post',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.post',
        ]);

        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/delete',
            'method' => 'DELETE',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.delete',
        ]);

        $postOnly = $this->doctor->auditLogsForMethod('POST');

        $this->assertCount(1, $postOnly->get());
        $this->assertEquals('POST', $postOnly->first()->method);
    }

    public function test_has_audit_logs_returns_boolean(): void
    {
        $this->assertFalse($this->doctor->hasAuditLogs());

        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/test',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test',
        ]);

        $this->assertTrue($this->doctor->hasAuditLogs());
    }

    public function test_audit_logs_count_attribute(): void
    {
        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/first',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.first',
        ]);

        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/second',
            'method' => 'PUT',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.second',
        ]);

        $this->assertEquals(2, $this->doctor->audit_logs_count);
    }

    public function test_paginated_audit_logs(): void
    {
        for ($i = 0; $i < 25; $i++) {
            AuditLog::create([
                'auditable_type' => Doctor::class,
                'auditable_id' => $this->doctor->id,
                'url' => "/admin/action/{$i}",
                'method' => 'POST',
                'payload' => null,
                'ip' => '127.0.0.1',
                'route_name' => "admin.action.{$i}",
            ]);
        }

        $paginated = $this->doctor->paginatedAuditLogs(10);

        $this->assertEquals(10, $paginated->perPage());
        $this->assertEquals(25, $paginated->total());
    }

    public function test_audit_logs_between_dates(): void
    {
        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/old',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.old',
            'created_at' => now()->subDays(10),
        ]);

        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/recent',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.recent',
            'created_at' => now()->subDays(2),
        ]);

        $between = $this->doctor->auditLogsBetween(
            now()->subDays(5),
            now()
        );

        $this->assertCount(1, $between->get());
        $this->assertEquals('admin.recent', $between->first()->route_name);
    }
}
