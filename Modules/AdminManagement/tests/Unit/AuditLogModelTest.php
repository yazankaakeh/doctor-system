<?php

namespace Modules\AdminManagement\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AdminManagement\Models\AuditLog;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\MedicalSpecialty;
use Tests\TestCase;

class AuditLogModelTest extends TestCase
{
    use RefreshDatabase;

    private Doctor $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $specialty = MedicalSpecialty::factory()->create();
        $this->doctor = Doctor::factory()->create([
            'medical_specialty_id' => $specialty->id,
        ]);
    }

    public function test_audit_log_can_be_created(): void
    {
        $auditLog = AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/test',
            'method' => 'POST',
            'payload' => json_encode(['key' => 'value']),
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test.store',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'url' => '/admin/test',
            'method' => 'POST',
        ]);
    }

    public function test_audit_log_has_auditable_relationship(): void
    {
        $auditLog = AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/test',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test.store',
        ]);

        $this->assertInstanceOf(Doctor::class, $auditLog->auditable);
        $this->assertEquals($this->doctor->id, $auditLog->auditable->id);
    }

    public function test_audit_log_payload_is_cast_to_array(): void
    {
        $payload = ['name' => 'Test', 'email' => 'test@example.com'];

        $auditLog = AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/test',
            'method' => 'POST',
            'payload' => $payload,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test.store',
        ]);

        $this->assertIsArray($auditLog->payload);
        $this->assertEquals('Test', $auditLog->payload['name']);
    }

    public function test_index_filter_by_auditable_type(): void
    {
        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/test',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test.store',
        ]);

        $query = AuditLog::query();
        $filtered = AuditLog::IndexFilter($query, ['auditable_type' => Doctor::class]);

        $this->assertEquals(1, $filtered->count());
    }

    public function test_index_filter_by_method(): void
    {
        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/test',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test.store',
        ]);

        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/test',
            'method' => 'DELETE',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test.delete',
        ]);

        $query = AuditLog::query();
        $filtered = AuditLog::IndexFilter($query, ['method' => 'POST']);

        $this->assertEquals(1, $filtered->count());
    }

    public function test_index_filter_by_route_name(): void
    {
        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/test',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test.store',
        ]);

        $query = AuditLog::query();
        $filtered = AuditLog::IndexFilter($query, ['route_name' => 'admin.test.store']);

        $this->assertEquals(1, $filtered->count());
    }

    public function test_get_auditable_types_returns_array(): void
    {
        $types = AuditLog::GetAuditableTypes();

        $this->assertIsArray($types);
        $this->assertArrayHasKey(Doctor::class, $types);
    }

    public function test_get_auditable_models_returns_doctors(): void
    {
        $models = AuditLog::GetAuditableModels();

        $this->assertCount(1, $models);
        $this->assertEquals($this->doctor->id, $models->first()->id);
    }

    public function test_filter_by_auditable_id(): void
    {
        AuditLog::create([
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'url' => '/admin/test',
            'method' => 'POST',
            'payload' => null,
            'ip' => '127.0.0.1',
            'route_name' => 'admin.test.store',
        ]);

        $query = AuditLog::query();
        $filtered = AuditLog::filter($query, [
            'auditable_type' => Doctor::class,
            'auditable_id' => $this->doctor->id,
            'created_at' => null,
        ]);

        $this->assertEquals(1, $filtered->count());
    }
}
