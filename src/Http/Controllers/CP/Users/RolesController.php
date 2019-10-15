<?php

namespace Statamic\Http\Controllers\CP\Users;

use Statamic\Facades\Role;
use Statamic\CP\Column;
use Illuminate\Http\Request;
use Statamic\Facades\Permission;
use Statamic\Http\Controllers\CP\CpController;

class RolesController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('edit roles');

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->handle(),
                'title' => $role->title(),
                'handle' => $role->handle(),
                'permissions' => $role->isSuper() ? __('Super User') : $role->permissions()->count(),
                'edit_url' => cp_route('roles.edit', $role->handle())
            ];
        })->values();

        if ($request->wantsJson()) {
            return $roles;
        }

        return view('statamic::roles.index', [
            'roles' => $roles,
            'columns' => [
                Column::make('title'),
                Column::make('handle'),
                Column::make('permissions'),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('edit roles');

        return view('statamic::roles.create', [
            'permissions' => $this->toGroupedTree(Permission::tree()),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('edit roles');

        $request->validate([
            'title' => 'required',
            'handle' => 'alpha_dash',
            'super' => 'boolean',
            'permissions' => 'array',
        ]);

        $role = Role::make()
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title))
            ->permissions($request->super ? ['super'] : $request->permissions)
            ->save();

        return ['redirect' => cp_route('roles.index', $role->handle())];
    }

    public function edit($role)
    {
        $this->authorize('edit roles');

        if (! $role = Role::find($role)) {
            return $this->pageNotFound();
        }

        return view('statamic::roles.edit', [
            'role' => $role,
            'super' => $role->isSuper(),
            'permissions' => $this->toGroupedTree(Permission::tree(), $role),
        ]);
    }

    public function update(Request $request, $role)
    {
        $this->authorize('edit roles');

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

        return ['redirect' => cp_route('roles.index', $role->handle())];
    }

    public function destroy($role)
    {
        $this->authorize('edit roles');

        if (! $role = Role::find($role)) {
            return $this->pageNotFound();
        }

        $role->delete();

        return response('', 204);
    }

    protected function toGroupedTree($tree, $role = null)
    {
        $tree = $this->toTree($tree, $role)
            ->groupBy('group')
            ->map(function ($permissions, $group) {
                return [
                    'handle' => $group,
                    'label' => __('statamic::permissions.group_'.$group),
                    'permissions' => $permissions
                ];
            });

        // Place ungrouped permissions at the end.
        if ($tree->has('misc')) {
            $tree->put('misc', $tree->pull('misc'));
        }

        return $tree->values();
    }

    protected function toTree($tree, $role = null)
    {
        return $tree->map(function ($item) use ($role) {
            $permission = $item['permission'];
            return [
                'value' => $permission->value(),
                'label' => $permission->label(),
                'description' => $permission->description(),
                'group' => $permission->group() ?? 'misc',
                'checked' => $role ? $role->hasPermission($permission->value()) : false,
                'children' => $this->toTree($item['children'], $role),
            ];
        });
    }
}
