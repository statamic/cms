<?php

namespace Statamic\Http\Controllers;

use Statamic\API\AssetContainer;
use Statamic\API\Collection;
use Statamic\API\GlobalSet;
use Statamic\API\Permission;
use Statamic\API\Role;
use Statamic\API\Str;
use Statamic\API\Helper;
use Statamic\API\Taxonomy;

class RolesController extends CpController
{
    public function index()
    {
        $this->authorize('super');

        return view('roles.index', [
            'title' => 'Roles'
        ]);
    }

    public function get()
    {
        $roles = [];

        foreach ($this->getRoles() as $key => $role) {
            $roles[] = [
                'title' => $role->title(),
                'edit_url' => route('user.role', $key),
                'uuid' => $role->uuid(), // @todo: remove.
                'id' => $role->uuid()
            ];
        }

        return ['columns' => ['title'], 'items' => $roles];
    }

    /**
     * @param $role
     * @return \Statamic\Contracts\Permissions\Role
     */
    private function getRole($role)
    {
        return array_get($this->getRoles(), $role);
    }

    /**
     * @return \Statamic\Contracts\Permissions\Role[]
     */
    public function getRoles()
    {
        return Role::all();
    }

    public function edit($role)
    {
        $this->authorize('super');

        $role = $this->getRole($role);

        $data = [
            'title' => 'Edit role',
            'role' => $role,
            'content_titles' => $this->getContentTitles(),
            'permissions' => Permission::structured(),
            'selected' => $this->getPermissions($role)
        ];

        return view('roles.edit', $data);
    }

    private function getContentTitles()
    {
        $titles = [];

        foreach (Collection::all() as $slug => $collection) {
            $titles['collections'][$slug] = $collection->title();
        }

        foreach (Taxonomy::all() as $slug => $taxonomy) {
            $titles['taxonomies'][$slug] = $taxonomy->title();
        }

        foreach (GlobalSet::all() as $global) {
            $titles['globals'][$global->slug()] = $global->title();
        }

        foreach (AssetContainer::all() as $id => $container) {
            $titles['assets'][$id] = $container->title();
        }

        return $titles;
    }

    /**
     * Get an array of permissions that have been added to the given role.
     *
     * @param null|\Statamic\Contracts\Permissions\Role $role
     * @return array
     */
    private function getPermissions($role = null)
    {
        $results = [];

        foreach (Permission::all() as $permission) {
            if ($role->hasPermission($permission)) {
                $results[] = $permission;
            }
        }

        return $results;
    }

    public function update($role)
    {
        $role = $this->getRole($role);

        $permissions = $this->request->input('permissions', []);

        $role->permissions($permissions);

        $title = $this->request->input('title');
        $role->title($title);
        $role->slug($this->request->input('slug', Str::slug($title)));

        $role->save();

        return redirect()->back()->with('success', 'Role updated.');
    }

    public function create()
    {
        $this->authorize('super');

        $data = [
            'title' => 'Create role',
            'content_titles' => $this->getContentTitles(),
            'permissions' => Permission::structured(),
            'selected' => []
        ];

        return view('roles.create', $data);
    }

    public function store()
    {
        $this->authorize('super');

        $title = $this->request->input('title');

        $data = [
            'title' => $title,
            'slug' => $this->request->input('slug', Str::snake($title)),
            'permissions' => $this->request->input('permissions', [])
        ];

        $role = app('Statamic\Contracts\Permissions\RoleFactory')->create($data);

        $role->save();

        return redirect()->route('user.role', $role->uuid())->with('success', 'Role updated.');
    }

    public function delete()
    {
        $this->authorize('super');

        $ids = Helper::ensureArray($this->request->input('ids'));

        foreach ($ids as $id) {
            Role::find($id)->delete();
        }

        return ['success' => true];
    }
}
