<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Role;
use Illuminate\Http\Request;
use Statamic\API\Permission;

class RolesController extends CpController
{
    public function index()
    {
        $this->authorize('super');

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->handle(),
                'title' => $role->title(),
                'edit_url' => cp_route('roles.edit', $role->handle())
            ];
        })->values();

        return view('statamic::roles.index', [
            'roles' => $roles
        ]);
    }

    public function create()
    {
        $this->authorize('super');

        return view('statamic::roles.create', [
            'permissions' => $this->toTreeArray(Permission::tree()),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('super');

        $request->validate([
            'title' => 'required',
            'handle' => 'alpha_dash',
            'super' => 'boolean',
            'permissions' => 'array',
        ]);

        $role = Role::create()
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title))
            ->permissions($request->super ? ['super'] : $request->permissions)
            ->save();

        return ['redirect' => cp_route('roles.edit', $role->handle())];
    }

    public function edit($role)
    {
        $this->authorize('super');

        if (! $role = Role::find($role)) {
            return $this->pageNotFound();
        }

        return view('statamic::roles.edit', [
            'role' => $role,
            'super' => $role->isSuper(),
            'permissions' => $this->toTreeArray(Permission::tree(), $role),
        ]);
    }

    public function update(Request $request, $role)
    {
        $this->authorize('super');

        if (! $role = Role::find($role)) {
            return $this->pageNotFound();
        }

        $request->validate([
            'title' => 'required',
            'handle' => 'alpha_dash',
            'super' => 'boolean',
            'permissions' => 'array',
        ]);

        $role
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title))
            ->permissions($request->super ? ['super'] : $request->permissions)
            ->save();

        return ['redirect' => cp_route('roles.edit', $role->handle())];
    }

    protected function toTreeArray($tree, $role = null)
    {
        return $tree->map(function ($item) use ($role) {
            $permission = $item['permission'];
            return [
                'value' => $permission->value(),
                'label' => $permission->label(),
                'checked' => $role ? $role->hasPermission($permission->value()) : false,
                'children' => $this->toTreeArray($item['children'], $role),
            ];
        });
    }
}
