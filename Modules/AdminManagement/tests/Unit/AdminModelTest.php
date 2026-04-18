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

    public function test_admin_can_be_created(): void
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@admin.com',
            'password' => 'password123',
            'is_active' => ActiveAdminEnum::ACTIVE->value,
        ]);

        $this->assertDatabaseHas('admins', [
            'email' => 'test@admin.com',
            'name' => 'Test Admin',
        ]);
    }

    public function test_admin_password_is_hashed(): void
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@admin.com',
            'password' => 'password123',
            'is_active' => ActiveAdminEnum::ACTIVE->value,
        ]);

        $this->assertNotEquals('password123', $admin->password);
    }

    public function test_admin_hidden_attributes(): void
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@admin.com',
            'password' => 'password123',
            'is_active' => ActiveAdminEnum::ACTIVE->value,
        ]);

        $array = $admin->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_admin_is_active_cast_to_enum(): void
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@admin.com',
            'password' => 'password123',
            'is_active' => ActiveAdminEnum::ACTIVE->value,
        ]);

        $this->assertInstanceOf(ActiveAdminEnum::class, $admin->is_active);
        $this->assertEquals(ActiveAdminEnum::ACTIVE, $admin->is_active);
    }

    public function test_admin_can_be_deactivated(): void
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@admin.com',
            'password' => 'password123',
            'is_active' => ActiveAdminEnum::ACTIVE->value,
        ]);

        $admin->update(['is_active' => ActiveAdminEnum::DE_ACTIVE->value]);

        $this->assertEquals(ActiveAdminEnum::DE_ACTIVE, $admin->fresh()->is_active);
    }

    public function test_admin_has_roles_trait(): void
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'test@admin.com',
            'password' => 'password123',
            'is_active' => ActiveAdminEnum::ACTIVE->value,
        ]);

        $role = Role::create(['name' => 'admin', 'guard_name' => 'admin']);
        $admin->assignRole($role);

        $this->assertTrue($admin->hasRole('admin'));
    }

    public function test_admin_fillable_fields(): void
    {
        $data = [
            'name' => 'Fillable Test',
            'email' => 'fillable@test.com',
            'password' => 'password',
            'img' => 'test.jpg',
            'is_active' => ActiveAdminEnum::ACTIVE->value,
        ];

        $admin = Admin::create($data);

        $this->assertEquals('Fillable Test', $admin->name);
        $this->assertEquals('fillable@test.com', $admin->email);
        $this->assertEquals('test.jpg', $admin->img);
    }
}
