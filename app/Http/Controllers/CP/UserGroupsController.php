<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Role;
use Statamic\API\User;
use Statamic\API\UserGroup;
use Illuminate\Http\Request;

class UserGroupsController extends CpController
{
    public function index()
    {
        $this->access('super');

        $groups = UserGroup::all()->map(function ($group) {
            return [
                'id' => $group->handle(),
                'title' => $group->title(),
                'handle' => $group->handle(),
                'users' => $group->users()->count(),
                'roles' => $group->roles()->count(),
                'edit_url' => cp_route('user-groups.edit', $group->handle())
            ];
        })->values();

        return view('statamic::usergroups.index', [
            'groups' => $groups
        ]);
    }

    public function edit($group)
    {
        $this->authorize('super');

        if (! $group = UserGroup::find($group)) {
            return $this->pageNotFound();
        }

        return view('statamic::usergroups.edit', [
            'group' => $group,
            'roles' => $group->roles()->map->handle()->values()->all(),
            'users' => $group->users()->map->id()->values()->all(),
            'roleSuggestions' => $this->roleSuggestions(),
            'userSuggestions' => $this->userSuggestions(),
        ]);
    }

    public function update(Request $request, $group)
    {
        $this->authorize('super');

        if (! $group = UserGroup::find($group)) {
            return $this->pageNotFound();
        }

        $request->validate([
            'title' => 'required',
            'handle' => 'alpha_dash',
            'roles' => 'required|array',
            'users' => 'array',
        ]);

        $group
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title))
            ->roles($request->roles)
            ->users($request->users)
            ->save();

        return ['redirect' => cp_route('user-groups.edit', $group->handle())];
    }

    public function create()
    {
        $this->authorize('super');

        return view('statamic::usergroups.create', [
            'roleSuggestions' => $this->roleSuggestions(),
            'userSuggestions' => $this->userSuggestions(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('super');

        $request->validate([
            'title' => 'required',
            'handle' => 'alpha_dash',
            'roles' => 'required|array',
            'users' => 'array',
        ]);

        $group = UserGroup::create()
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title))
            ->roles($request->roles)
            ->users($request->users)
            ->save();

        return ['redirect' => cp_route('user-groups.edit', $group->handle())];
    }

    public function destroy($group)
    {
        $this->authorize('super');

        if (! $group = UserGroup::find($group)) {
            return $this->pageNotFound();
        }

        $group->delete();

        return response('', 204);
    }

    protected function roleSuggestions()
    {
        // TODO: Remove this and replace with the user roles fieldtype once it has been fixed for v3.
        return Role::all()->map(function ($role) {
            return [
                'text' => $role->title(),
                'value' => $role->id(),
            ];
        })->values()->all();
    }

    protected function userSuggestions()
    {
        // TODO: Remove this and replace with the users fieldtype once it has been fixed for v3.
        return User::all()->map(function ($user) {
            return [
                'text' => $user->username(),
                'value' => $user->id(),
            ];
        })->values()->all();
    }
}
