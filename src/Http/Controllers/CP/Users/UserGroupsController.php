<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Middleware\RequireStatamicPro;
use Statamic\Support\Str;

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

        if ($groups->count() === 0) {
            return view('statamic::usergroups.empty');
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
            'filters' => Scope::filters('usergroup-users', [
                'blueprints' => ['user'],
            ]),
        ]);
    }

    public function edit(Request $request, $group)
    {
        $this->authorize('edit user groups');

        if (! $group = UserGroup::find($group)) {
            return $this->pageNotFound();
        }

        $blueprint = $group->blueprint();

        if (! User::current()->can('assign roles')) {
            $blueprint->ensureField('roles', ['visibility' => 'read_only']);
        }

        $fields = $blueprint
            ->fields()
            ->addValues($group->data()->merge([
                'title' => $group->title(),
                'handle' => $group->handle(),
                'roles' => $group->roles()->map->handle()->values()->all(),
            ])->all())
            ->preProcess();

        $viewData = [
            'title' => $group->title(),
            'values' => $fields->values()->all(),
            'meta' => $fields->meta(),
            'blueprint' => $group->blueprint()->toPublishArray(),
            'reference' => $group->handle(),
            'actions' => [
                'save' => $group->updateUrl(),
                'editBlueprint' => cp_route('blueprints.user-groups.edit'),
            ],
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::usergroups.edit', $viewData);
    }

    public function update(Request $request, $group)
    {
        $this->authorize('edit user groups');

        if (! $group = UserGroup::find($group)) {
            return $this->pageNotFound();
        }

        $fields = $group->blueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->except(['title', 'handle', 'roles']);

        foreach ($values as $key => $value) {
            $group->set($key, $value);
        }

        $group->title($request->title);

        if ($request->handle) {
            $group->handle($request->handle);
        }

        if (User::current()->can('assign roles')) {
            $group->roles($request->roles);
        }

        $group->save();

        return ['title' => $group->title()];
    }

    public function create()
    {
        $this->authorize('edit user groups');

        $blueprint = UserGroup::blueprint();

        if (! User::current()->can('edit roles')) {
            $blueprint->ensureField('roles', ['visibility' => 'read_only']);
        }

        $fields = $blueprint
            ->fields()
            ->preProcess();

        $viewData = [
            'values' => $fields->values()->all(),
            'meta' => $fields->meta(),
            'blueprint' => $blueprint->toPublishArray(),
            'actions' => [
                'save' => cp_route('user-groups.store'),
            ],
        ];

        return view('statamic::usergroups.create', $viewData);
    }

    public function store(Request $request)
    {
        $this->authorize('edit user groups');

        $blueprint = UserGroup::blueprint();

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->except(['title', 'handle', 'roles']);

        $handle = $request->handle ?: Str::snake($request->title);

        if (UserGroup::find($handle)) {
            $error = __('A User Group with that handle already exists.');

            if ($request->wantsJson()) {
                return response()->json(['message' => $error], 422);
            }

            return back()->withInput()->with('error', $error);
        }

        $group = UserGroup::make()
            ->title($request->title)
            ->data($values)
            ->handle($handle);

        if (User::current()->can('assign roles')) {
            $group->roles($request->roles);
        }

        $group->save();

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
