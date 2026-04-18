<?php

namespace Modules\AdminManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\AdminManagement\Http\Requests\PermissionsRequest;
use Modules\AdminManagement\Repository\Role\RoleInterface;
use Modules\AdminManagement\Repository\Role\RoleRepository;

class RoleManagementController extends Controller
{
    public function __construct(public RoleInterface $roleRepository) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        /* @var RoleRepository $repo */
        $repo = $this->roleRepository;
        $roles = $repo->index();

        return view('adminmanagement::roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        /* @var RoleRepository $repo */
        $repo = $this->roleRepository;
        $permissions = $repo->create();

        return view('adminmanagement::roles.create', compact('permissions'));
    }

    public function edit(Request $request, $id): View|\Illuminate\Foundation\Application|Factory|Application
    {
        /* @var RoleRepository $repo */
        $repo = $this->roleRepository;
        $data = $repo->edit($id);
        $permissions = $data['permissions'];
        $userPermissions = $data['userPermissions'];
        $item = $data['item'];

        return view(
            'adminmanagement::roles.edit',
            compact('permissions', 'userPermissions', 'item'),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermissionsRequest $request, $id): RedirectResponse
    {
        /* @var RoleRepository $repo */
        $repo = $this->roleRepository;
        $repo->update($request, $id);
        $request->session()->flash('success', 'created Successfully');

        return redirect()->route('admin.role_management.index')->with(
            'success',
            trans('mps::mps.success.updatedSuccess'),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        /* @var RoleRepository $repo */
        $repo = $this->roleRepository;
        $repo->destroy($id);
        $request->session()->flash('success', 'created Successfully');

        return redirect()->route('admin.role_management.index')->with(
            'success',
            trans('mps::mps.success.deletedSuccess'),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PermissionsRequest $request): RedirectResponse
    {
        /* @var RoleRepository $repo */
        $repo = $this->roleRepository;
        $repo->store($request);
        $request->session()->flash('success', 'created Successfully');

        return redirect()->route('admin.role_management.index')->with(
            'success',
            trans('mps::mps.success.createdSuccess'),
        );
    }
}
