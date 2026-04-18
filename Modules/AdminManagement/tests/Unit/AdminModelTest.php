<?php

namespace Modules\AdminManagement\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AdminManagement\app\Models\Admin;
use Modules\AdminManagement\Enums\ActiveAdminEnum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a valid Admin payload. The migration declares `phone` as NOT NULL
     * with no default, so every test must supply one to satisfy the schema.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function adminAttributes(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Admin',
            'email' => 'test@admin.com',
            'phone' => '+15555550100',
            'password' => 'password123',
            'is_active' => ActiveAdminEnum::ACTIVE->value,
        ], $overrides);
    }

    public function test_admin_can_be_created(): void
    {
        Admin::create($this->adminAttributes());

        $this->assertDatabaseHas('admins', [
            'email' => 'test@admin.com',
            'name' => 'Test Admin',
            'phone' => '+15555550100',
        ]);
    }

    public function test_admin_password_is_hashed(): void
    {
        $admin = Admin::create($this->adminAttributes());

        $this->assertNotEquals('password123', $admin->password);
        $this->assertTrue(password_verify('password123', $admin->password));
    }

    public function test_admin_hidden_attributes(): void
    {
        $admin = Admin::create($this->adminAttributes());
        $array = $admin->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_admin_is_active_cast_to_enum(): void
    {
        $admin = Admin::create($this->adminAttributes());

        $this->assertInstanceOf(ActiveAdminEnum::class, $admin->is_active);
        $this->assertEquals(ActiveAdminEnum::ACTIVE, $admin->is_active);
    }

    public function test_admin_can_be_deactivated(): void
    {
        $admin = Admin::create($this->adminAttributes());

        $admin->update(['is_active' => ActiveAdminEnum::DE_ACTIVE->value]);

        $this->assertEquals(ActiveAdminEnum::DE_ACTIVE, $admin->fresh()->is_active);
    }

    public function test_admin_has_roles_trait(): void
    {
        $admin = Admin::create($this->adminAttributes());

        // Match the model's declared guard (`admin`) so the role's guard
        // matches at assign time.
        $role = Role::create(['name' => 'admin', 'guard_name' => 'admin']);
        $admin->assignRole($role);

        $this->assertTrue($admin->hasRole('admin'));
    }

    public function test_admin_fillable_fields(): void
    {
        $admin = Admin::create($this->adminAttributes([
            'name' => 'Fillable Test',
            'email' => 'fillable@test.com',
            'phone' => '+15555550101',
            'img' => 'test.jpg',
        ]));

        $this->assertSame('Fillable Test', $admin->name);
        $this->assertSame('fillable@test.com', $admin->email);
        $this->assertSame('+15555550101', $admin->phone);
        $this->assertSame('test.jpg', $admin->img);
    }
}
