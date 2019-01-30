<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Role;
use Statamic\API\User;
use Statamic\API\Action;
use Statamic\API\Filter;
use Statamic\API\UserGroup;
use Illuminate\Http\Request;

class UserGroupsController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('super');

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

        if ($request->wantsJson()) {
            return $groups;
        }

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
            'filters' => Filter::for('usergroup-users'),
            'actions' => Action::for('usergroup-users'),
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
        ]);

        $group
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title))
            ->roles($request->roles)
            ->save();

        return ['redirect' => cp_route('user-groups.edit', $group->handle())];
    }

    public function create()
    {
        $this->authorize('super');

        return view('statamic::usergroups.create');
    }

    public function store(Request $request)
    {
        $this->authorize('super');

        $request->validate([
            'title' => 'required',
            'handle' => 'alpha_dash',
            'roles' => 'required|array',
        ]);

        $group = UserGroup::create()
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title))
            ->roles($request->roles)
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
}
