<?php

namespace Modules\AdminManagement\Tests\Feature;

use Modules\AdminManagement\Tests\AdminManagementTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementTest extends AdminManagementTestCase
{
    public function test_guest_cannot_access_role_management(): void
    {
        $response = $this->get(route('admin.role_management.index'));

        $response->assertRedirect();
    }

    public function test_authenticated_admin_can_view_role_list(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.role_management.index'));

        $response->assertOk();
        $response->assertViewIs('adminmanagement::roles.index');
        $response->assertViewHas('roles');
    }

    public function test_authenticated_admin_can_view_create_role_form(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.role_management.create'));

        $response->assertOk();
        $response->assertViewIs('adminmanagement::roles.create');
        $response->assertViewHas('permissions');
    }

    public function test_admin_can_create_new_role(): void
    {
        $permissions = Permission::where('guard_name', 'doctor')
            ->take(3)
            ->pluck('name')
            ->toArray();

        $roleData = [
            'name' => 'New Custom Role',
            'permissions' => $permissions,
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.role_management.store'), $roleData);

        $response->assertRedirect(route('admin.role_management.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'name' => 'New Custom Role',
            'guard_name' => 'doctor',
        ]);

        $role = Role::where('name', 'New Custom Role')->first();
        $this->assertTrue($role->hasPermissionTo($permissions[0]));
    }

    public function test_authenticated_admin_can_view_edit_role_form(): void
    {
        $role = $this->createTestRole('Edit Form Role');

        $response = $this->actingAsAdmin()
            ->get(route('admin.role_management.edit', $role->id));

        $response->assertOk();
        $response->assertViewIs('adminmanagement::roles.edit');
        $response->assertViewHas(['permissions', 'userPermissions', 'item']);
    }

    public function test_admin_can_update_existing_role(): void
    {
        $role = $this->createTestRole('Original Role Name');
        $newPermissions = Permission::where('guard_name', 'doctor')
            ->take(2)
            ->pluck('name')
            ->toArray();

        $updateData = [
            'name' => 'Updated Role Name',
            'permissions' => $newPermissions,
        ];

        $response = $this->actingAsAdmin()
            ->put(route('admin.role_management.update', $role->id), $updateData);

        $response->assertRedirect(route('admin.role_management.index'));
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertEquals('Updated Role Name', $role->name);
    }

    public function test_admin_can_delete_role(): void
    {
        $role = $this->createTestRole('Delete Me Role');
        $roleId = $role->id;

        $response = $this->actingAsAdmin()
            ->delete(route('admin.role_management.destroy', $roleId));

        $response->assertRedirect(route('admin.role_management.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    }

    public function test_create_role_validation_requires_name(): void
    {
        $roleData = [
            'permissions' => ['admin.user_management.index'],
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.role_management.store'), $roleData);

        $response->assertSessionHasErrors('name');
    }

    public function test_create_role_validation_requires_unique_name(): void
    {
        $existingRole = $this->createTestRole('Unique Role Name');

        $roleData = [
            'name' => 'Unique Role Name',
            'permissions' => ['admin.user_management.index'],
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.role_management.store'), $roleData);

        $response->assertSessionHasErrors('name');
    }

    public function test_create_role_validation_requires_permissions(): void
    {
        $roleData = [
            'name' => 'No Permissions Role',
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.role_management.store'), $roleData);

        $response->assertSessionHasErrors('permissions');
    }

    public function test_create_role_validation_requires_valid_permissions(): void
    {
        $roleData = [
            'name' => 'Invalid Permissions Role',
            'permissions' => ['non.existent.permission'],
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.role_management.store'), $roleData);

        $response->assertSessionHasErrors('permissions.0');
    }

    public function test_update_role_allows_same_name_for_current_role(): void
    {
        $role = $this->createTestRole('Same Name Role');
        $permissions = Permission::where('guard_name', 'doctor')
            ->take(2)
            ->pluck('name')
            ->toArray();

        $updateData = [
            'name' => 'Same Name Role', // Same name
            'permissions' => $permissions,
        ];

        $response = $this->actingAsAdmin()
            ->put(route('admin.role_management.update', $role->id), $updateData);

        $response->assertRedirect(route('admin.role_management.index'));
        $response->assertSessionHas('success');
    }

    public function test_role_list_shows_pagination(): void
    {
        // Create multiple roles
        for ($i = 0; $i < 20; $i++) {
            $this->createTestRole("Role Number {$i}");
        }

        $response = $this->actingAsAdmin()
            ->get(route('admin.role_management.index'));

        $response->assertOk();
        $response->assertViewHas('roles');
    }

    public function test_role_permissions_are_synced_on_update(): void
    {
        $initialPermissions = Permission::where('guard_name', 'doctor')
            ->take(3)
            ->pluck('name')
            ->toArray();

        $role = $this->createTestRole('Sync Permissions Role', $initialPermissions);
        $this->assertCount(3, $role->permissions);

        $newPermissions = Permission::where('guard_name', 'doctor')
            ->skip(3)
            ->take(2)
            ->pluck('name')
            ->toArray();

        $updateData = [
            'name' => $role->name,
            'permissions' => $newPermissions,
        ];

        $response = $this->actingAsAdmin()
            ->put(route('admin.role_management.update', $role->id), $updateData);

        $response->assertRedirect(route('admin.role_management.index'));

        $role->refresh();
        $this->assertCount(2, $role->permissions);
    }

    public function test_deleted_role_removes_associations(): void
    {
        $role = $this->createTestRole('Association Role');
        $doctor = $this->createTestDoctor(['email' => 'roletest@hospital.com']);

        // Assign additional role to doctor
        $doctor->assignRole($role);
        $this->assertTrue($doctor->hasRole($role->name));

        // Delete the role
        $this->actingAsAdmin()
            ->delete(route('admin.role_management.destroy', $role->id));

        $doctor->refresh();
        $this->assertFalse($doctor->hasRole('Association Role'));
    }
}
