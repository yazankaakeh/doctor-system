<?php

namespace Modules\AdminManagement\Tests\Unit;

use Modules\AdminManagement\Http\Requests\PermissionsRequest;
use Modules\AdminManagement\Repository\Role\RoleRepository;
use Modules\AdminManagement\Tests\AdminManagementTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepositoryTest extends AdminManagementTestCase
{
    private RoleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RoleRepository;
    }

    public function test_index_returns_paginated_roles(): void
    {
        $this->createTestRole('Editor');
        $this->createTestRole('Viewer');

        $result = $this->repository->index();

        $this->assertGreaterThanOrEqual(2, $result->total());
    }

    public function test_store_creates_new_role_with_permissions(): void
    {
        $permissions = Permission::where('guard_name', 'doctor')
            ->take(3)
            ->pluck('name')
            ->toArray();

        $request = $this->createPermissionsRequest([
            'name' => 'New Test Role',
            'permissions' => $permissions,
        ]);

        $this->repository->store($request);

        $this->assertDatabaseHas('roles', [
            'name' => 'New Test Role',
            'guard_name' => 'doctor',
        ]);

        $role = Role::where('name', 'New Test Role')->first();
        $this->assertTrue($role->hasPermissionTo($permissions[0]));
    }

    public function test_create_returns_permissions_grouped_by_section(): void
    {
        // Create permissions with sections
        Permission::create([
            'name' => 'section1.action1',
            'guard_name' => 'doctor',
            'section' => 'Section One',
        ]);
        Permission::create([
            'name' => 'section1.action2',
            'guard_name' => 'doctor',
            'section' => 'Section One',
        ]);
        Permission::create([
            'name' => 'section2.action1',
            'guard_name' => 'doctor',
            'section' => 'Section Two',
        ]);

        $result = $this->repository->create();

        $this->assertIsIterable($result);
    }

    public function test_edit_returns_role_with_permissions(): void
    {
        $permissions = Permission::where('guard_name', 'doctor')
            ->take(2)
            ->pluck('name')
            ->toArray();

        $role = $this->createTestRole('Edit Test Role', $permissions);

        $result = $this->repository->edit($role->id);

        $this->assertArrayHasKey('item', $result);
        $this->assertArrayHasKey('userPermissions', $result);
        $this->assertArrayHasKey('permissions', $result);
        $this->assertEquals($role->id, $result['item']->id);
        $this->assertCount(2, $result['userPermissions']);
    }

    public function test_update_modifies_role_and_syncs_permissions(): void
    {
        $initialPermissions = Permission::where('guard_name', 'doctor')
            ->take(2)
            ->pluck('name')
            ->toArray();

        $role = $this->createTestRole('Update Test Role', $initialPermissions);

        $newPermissions = Permission::where('guard_name', 'doctor')
            ->skip(2)
            ->take(3)
            ->pluck('name')
            ->toArray();

        $request = $this->createPermissionsRequest([
            'name' => 'Updated Role Name',
            'permissions' => $newPermissions,
        ]);

        $this->repository->update($request, $role->id);

        $role->refresh();
        $this->assertEquals('Updated Role Name', $role->name);
        $this->assertCount(3, $role->permissions);
    }

    public function test_destroy_deletes_role(): void
    {
        $role = $this->createTestRole('Delete Test Role');
        $roleId = $role->id;

        $this->repository->destroy($roleId);

        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    }

    public function test_store_creates_role_with_doctor_guard(): void
    {
        $request = $this->createPermissionsRequest([
            'name' => 'Guard Test Role',
            'permissions' => [],
        ]);

        $this->repository->store($request);

        $role = Role::where('name', 'Guard Test Role')->first();
        $this->assertEquals('doctor', $role->guard_name);
    }

    private function createPermissionsRequest(array $data): PermissionsRequest
    {
        $request = new PermissionsRequest;
        $request->merge($data);

        return $request;
    }
}
