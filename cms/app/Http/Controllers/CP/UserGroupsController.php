<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Str;
use Statamic\API\Helper;
use Statamic\API\UserGroup;

class UserGroupsController extends CpController
{
    public function index()
    {
        $this->access('super');

        return view('usergroups.index', [
            'title' => 'Roles'
        ]);
    }

    public function get()
    {
        $rows = [];

        foreach ($this->getGroups() as $key => $group) {
            $rows[] = [
                'title' => $group->title(),
                'id' => $group->id(),
                'users' => $group->users()->count(),
                'edit_url' => $group->editUrl()
            ];
        }

        return ['columns' => ['title'], 'items' => $rows];
    }

    /**
     * @param $group
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    private function getGroup($group)
    {
        return array_get($this->getGroups(), $group);
    }

    /**
     * @return \Statamic\Contracts\Permissions\UserGroup[]
     */
    public function getGroups()
    {
        return UserGroup::all();
    }

    public function edit($group)
    {
        $this->authorize('super');

        $group = $this->getGroup($group);

        $roles = $group->roles()->map(function($role) {
            return $role->uuid();
        })->values();

        $users = $group->users()->filter(function ($user) {
            return $user !== null;
        })->map(function ($user) {
            return $user->id();
        })->all();

        $data = compact('group', 'roles', 'users');
        $data['title'] = 'Edit group';

        return view('usergroups.edit', $data);
    }

    public function update($group)
    {
        $this->authorize('super');

        $group = $this->getGroup($group);

        $title = $this->request->input('title');
        $group->title($title);
        $group->slug($this->request->input('slug', Str::slug($title)));

        if ($roles = $this->request->input('roles')) {
            $roles = json_decode($roles);
        } else {
            $roles = [];
        }

        $group->roles($roles);

        if ($users = $this->request->input('users')) {
            $users = json_decode($users);
        } else {
            $users = [];
        }

        $group->users($users);

        $group->save();

        return redirect()->route('user.group', $group->id())
                         ->with('success', 'Group updated.');
    }

    public function create()
    {
        $this->authorize('super');

        $data = [
            'title' => 'Create group',
            'roles' => [],
            'users' => []
        ];

        return view('usergroups.create', $data);
    }

    public function store()
    {
        $this->authorize('super');

        $title = $this->request->input('title');

        if ($roles = $this->request->input('roles')) {
            $roles = json_decode($roles);
        } else {
            $roles = [];
        }

        if ($users = $this->request->input('users')) {
            $users = json_decode($users);
        } else {
            $users = [];
        }

        $data = [
            'title' => $title,
            'roles' => $roles,
            'users' => $users
        ];

        $group = app('Statamic\Contracts\Permissions\UserGroupFactory')->create($data);

        $group->save();

        return redirect()->route('user.group', $group->uuid())->with('success', 'Group created.');
    }

    public function delete()
    {
        $this->authorize('super');

        $ids = Helper::ensureArray($this->request->input('ids'));

        foreach ($ids as $id) {
            UserGroup::find($id)->delete();
        }

        return ['success' => true];
    }
}
