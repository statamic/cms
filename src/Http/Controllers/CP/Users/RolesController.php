<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\CP\Column;
use Statamic\Facades\Permission;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Middleware\RequireStatamicPro;

class RolesController extends CpController
{
    public function __construct()
    {
        $this->middleware(RequireStatamicPro::class);
    }

    public function index(Request $request)
    {
        $this->authorize('edit roles');

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->handle(),
                'title' => $role->title(),
                'handle' => $role->handle(),
                'permissions' => $role->isSuper() ? __('Super User') : $role->permissions()->count(),
                'edit_url' => $role->editUrl(),
                'delete_url' => $role->deleteUrl(),
            ];
        })->values();

        if ($request->wantsJson()) {
            return $roles;
        }

        return view('statamic::roles.index', [
            'roles' => $roles,
            'columns' => [
                Column::make('title')->label(__('Title')),
                Column::make('handle')->label(__('Handle')),
                Column::make('permissions')->label(__('Permissions')),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('edit roles');

        return view('statamic::roles.create', [
            'permissions' => $this->updateTree(Permission::tree()),
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
            ->handle($request->handle ?: snake_case($request->title));

        if ($request->super && User::current()->isSuper()) {
            $role->permissions(['super']);
        } elseif (! in_array('super', $request->permissions ?? [])) {
            $role->permissions($request->permissions);
        }

        $role->save();

        session()->flash('success', __('Role created'));

        return ['redirect' => cp_route('roles.index')];
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
            'permissions' => $this->updateTree(Permission::tree(), $role),
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
            ->handle($request->handle ?: snake_case($request->title));

        if ($request->super && User::current()->isSuper()) {
            $role->permissions(['super']);
        } elseif (! in_array('super', $request->permissions ?? [])) {
            $role->permissions($request->permissions);
        }

        $role->save();

        session()->flash('success', __('Role updated'));

        return ['redirect' => cp_route('roles.index')];
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

    protected function updateTree($tree, $role = null)
    {
        return $tree->map(function ($group) use ($role) {
            return array_merge($group, [
                'permissions' => $this->updatePermissions($group['permissions'], $role),
            ]);
        });
    }

    protected function updatePermissions($permissions, $role = null)
    {
        return collect($permissions)->map(function ($item) use ($role) {
            return array_merge($item, [
                'checked' => $role ? $role->hasPermission($item['value']) : false,
                'children' => $this->updatePermissions($item['children'], $role),
            ]);
        })->all();
    }
}
