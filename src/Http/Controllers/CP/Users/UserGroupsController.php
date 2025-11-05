<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Inertia\Inertia;
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

        return Inertia::render('user-groups/Index', [
            'groups' => $groups,
            'createUrl' => cp_route('user-groups.create'),
            'editBlueprintUrl' => cp_route('blueprints.user-groups.edit'),
            'canCreate' => User::current()->can('edit user groups'),
            'canConfigureFields' => User::current()->can('configure fields'),
        ]);
    }

    public function show($group)
    {
        $this->authorize('edit user groups');

        if (! $group = UserGroup::find($group)) {
            return $this->pageNotFound();
        }

        return Inertia::render('user-groups/Show', [
            'group' => [
                'id' => $group->handle(),
                'title' => $group->title(),
                'handle' => $group->handle(),
                'editUrl' => $group->editUrl(),
                'deleteUrl' => $group->deleteUrl(),
                'canEdit' => User::current()->can('edit', $group),
                'canDelete' => User::current()->can('delete', $group),
            ],
            'filters' => Scope::filters('usergroup-users', [
                'blueprints' => ['user'],
            ]),
            'listingConfig' => [
                'listingKey' => 'usergroup-users',
                'groupId' => $group->id(),
                'actionUrl' => cp_route('users.actions.run'),
                'allowFilterPresets' => false,
            ],
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

        return Inertia::render('user-groups/Edit', array_merge($viewData, [
            'canEditBlueprint' => User::current()->can('configure fields'),
        ]));
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

    public function create(Request $request)
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
            'canEditBlueprint' => User::current()->can('configure fields'),
            'isCreating' => true,
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return Inertia::render('user-groups/Create', array_merge($viewData, [
            'title' => __('Create User Group'),
        ]));
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
