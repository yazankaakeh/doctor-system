<?php

namespace Tests\Feature\AdminManagement;

use Modules\AdminManagement\Models\AuditLog;
use Modules\Doctor\Models\Doctor;
use Tests\TestCase;

/**
 * Regression test for the AuditLog::doctor relation, which previously
 * crashed with "Unknown column 'auditable_type' in 'where clause'" because
 * a stray ->where() was chained onto a belongsTo definition.
 *
 * The new accessor must:
 *   - return null when the audit row's auditable_type isn't Doctor
 *   - never produce broken SQL
 */
class AuditLogDoctorAccessorTest extends TestCase
{
    /**
     * @test
     */
    public function accessor_returns_null_when_auditable_type_is_not_doctor(): void
    {
        $audit = new AuditLog;
        $audit->setRawAttributes([
            'id' => 1,
            'auditable_type' => 'App\\Models\\SomethingElse',
            'auditable_id' => 1,
        ]);

        // Calling ->doctor on an audit whose type is not Doctor must NOT
        // execute the broken `... AND auditable_type = ...` query, and must
        // return null cleanly.
        $this->assertNull($audit->doctor);
    }

    /**
     * @test
     */
    public function accessor_returns_null_when_auditable_id_is_missing(): void
    {
        $audit = new AuditLog;
        $audit->setRawAttributes([
            'id' => 2,
            'auditable_type' => Doctor::class,
            'auditable_id' => null,
        ]);

        // No id to look up → no DB hit → null returned.
        $this->assertNull($audit->doctor);
    }

    /**
     * @test
     */
    public function the_polymorphic_auditable_relation_is_defined(): void
    {
        $audit = new AuditLog;

        $this->assertTrue(
            method_exists($audit, 'auditable'),
            'AuditLog must expose a polymorphic `auditable` relation for the index view.',
        );
    }
}
