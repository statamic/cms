<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Middleware\RequireStatamicPro;

class UserGroupsController extends CpController
{
    public function __construct()
    {
        $this->middleware(RequireStatamicPro::class);
    }

    public function index(Request $request)
    {
        $this->authorize('edit user groups');

        $groups = UserGroup::all()->map(function ($group) {
            return [
                'id' => $group->handle(),
                'title' => $group->title(),
                'handle' => $group->handle(),
                'users' => $group->users()->count(),
                'roles' => $group->roles()->count(),
                'show_url' => $group->showUrl(),
                'edit_url' => $group->editUrl(),
                'delete_url' => $group->deleteUrl(),
            ];
        })->values();

        if ($request->wantsJson()) {
            return $groups;
        }

        return view('statamic::usergroups.index', [
            'groups' => $groups,
        ]);
    }

    public function show($group)
    {
        $this->authorize('edit user groups');

        if (! $group = UserGroup::find($group)) {
            return $this->pageNotFound();
        }

        return view('statamic::usergroups.show', [
            'group' => $group,
            'filters' => Scope::filters('usergroup-users'),
        ]);
    }

    public function edit($group)
    {
        $this->authorize('edit user groups');

        if (! $group = UserGroup::find($group)) {
            return $this->pageNotFound();
        }

        return view('statamic::usergroups.edit', [
            'group' => $group,
            'roles' => $group->roles()->map->handle()->values()->all(),
            'filters' => Scope::filters('usergroup-users'),
        ]);
    }

    public function update(Request $request, $group)
    {
        $this->authorize('edit user groups');

        if (! $group = UserGroup::find($group)) {
            return $this->pageNotFound();
        }

        $request->validate([
            'title' => 'required',
            'handle' => 'alpha_dash',
            'roles' => 'array',
        ]);

        $group
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title));

        if (User::current()->can('assign roles')) {
            $group->roles($request->roles);
        }

        $group->save();

        session()->flash('success', __('User group updated'));

        return ['redirect' => cp_route('user-groups.show', $group->handle())];
    }

    public function create()
    {
        $this->authorize('edit user groups');

        return view('statamic::usergroups.create');
    }

    public function store(Request $request)
    {
        $this->authorize('edit user groups');

        $request->validate([
            'title' => 'required',
            'handle' => 'alpha_dash',
            'roles' => 'array',
        ]);

        $group = UserGroup::make()
            ->title($request->title)
            ->handle($request->handle ?: snake_case($request->title));

        if (User::current()->can('assign roles')) {
            $group->roles($request->roles);
        }

        $group->save();

        session()->flash('success', __('User group created'));

        return ['redirect' => cp_route('user-groups.show', $group->handle())];
    }

    public function destroy($group)
    {
        $this->authorize('edit user groups');

        if (! $group = UserGroup::find($group)) {
            return $this->pageNotFound();
        }

        $group->delete();

        return response('', 204);
    }
}
