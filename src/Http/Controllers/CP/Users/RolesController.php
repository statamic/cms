<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\CP\Column;
use Statamic\Facades\Permission;
use Statamic\Facades\Role;
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
            'permissions' => 'array',
        ]);

        $blueprint = Role::blueprint();

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->except(['title', 'handle', 'super', 'permissions']);

        var_dump($values);
        exit();

        $role = Role::make()
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title))
            ->permissions($request->super ? ['super'] : $request->permissions)
            ->data($values)
            ->save();

        session()->flash('success', __('Role created'));

        return ['redirect' => cp_route('roles.index', $role->handle())];
    }

    public function edit(Request $request, $role)
    {
        $this->authorize('edit roles');

        if (! $role = Role::find($role)) {
            return $this->pageNotFound();
        }

        $blueprint = $role->blueprint();

        $fields = $blueprint
            ->fields()
            ->addValues($role->data()->merge(['handle' => $role->handle(), 'title' => $role->title(), 'super' => $role->isSuper()])->all())
            ->preProcess();

        $viewData = [
            'role' => $role,
            'title' => $role->title(),
            'values' => $fields->values()->all(),
            'meta' => $fields->meta(),
            'blueprint' => $role->blueprint()->toPublishArray(),
            'reference' => $role->handle(),
            'actions' => [
                'save' => $role->updateUrl(),
                'editBlueprint' => cp_route('roles.blueprint.edit'),
            ],
            'super' => $role->isSuper(),
            'permissions' => $this->updateTree(Permission::tree(), $role),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::roles.edit', $viewData);
    }

    public function update(Request $request, $role)
    {
        $this->authorize('edit roles');

        if (! $role = Role::find($role)) {
            return $this->pageNotFound();
        }

        $request->validate([
            'permissions' => 'array',
        ]);

        $fields = $role->blueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->except(['title', 'handle', 'super', 'permissions']);

        foreach ($values as $key => $value) {
            $role->set($key, $value);
        }

        $role
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title))
            ->permissions($request->super ? ['super'] : $request->permissions)
            ->save();

        session()->flash('success', __('Role updated'));

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
