<?php

namespace Modules\AdminManagement\Repository\Role;

use App\Enum\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use LaravelIdea\Helper\Spatie\Permission\Models\_IH_Permission_C;
use Modules\AdminManagement\Http\Requests\PermissionsRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleInterface
{
    public function index(): LengthAwarePaginator
    {
        return Role::query()->paginate(Pagination::PAG->value);
    }

    public function store(PermissionsRequest $request): void
    {
        /* @var Role $role */
        $role = Role::query()->create([
            'name' => $request->name,
            'guard_name' => 'doctor',
        ]);
        $role->syncPermissions([$request->permissions]);
    }

    public function create(): \Illuminate\Database\Eloquent\Collection|array|_IH_Permission_C|Collection
    {
        return Permission::query()->get()
            ->sortBy('section')
            ->groupBy('section');
    }

    public function update(PermissionsRequest $request, int $id): void
    {
        /** @var Role $role */
        $role = Role::query()->findOrFail($id);
        $role->update(['name' => $request->name]);
        $role->syncPermissions([$request->permissions]);
    }

    public function edit(int $id): array
    {
        $role = Role::query()->findOrFail($id);
        $userPermissions = $role->permissions->pluck('name')->toArray();
        $permissions = Permission::query()->get()
            ->sortBy('section')
            ->groupBy('section');

        return [
            'item' => $role,
            'userPermissions' => $userPermissions,
            'permissions' => $permissions,
        ];
    }

    public function destroy(int $id): void
    {
        Role::query()->findOrFail($id)->delete();
    }
}
