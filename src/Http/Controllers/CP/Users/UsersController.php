<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\CP\Column;
use Statamic\Facades\Action;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Notifications\ActivateAccount;

class UsersController extends CpController
{
    /**
     * @var UserContract
     */
    private $user;

    public function index(FilteredRequest $request)
    {
        $this->authorize('index', UserContract::class);

        if ($request->wantsJson()) {
            return $this->json($request);
        }

        return view('statamic::users.index', [
            'filters' => Scope::filters('users', $context = [
                'blueprints' => ['user'],
            ]),
        ]);
    }

    protected function json($request)
    {
        $query = $request->group
            ? UserGroup::find($request->group)->queryUsers()
            : User::query();

        $this->filter($query, $request->filters);

        $users = $query
            ->orderBy($sort = request('sort', 'email'), request('order', 'asc'))
            ->paginate(request('perPage'))
            ->supplement(function ($user) use ($request) {
                return [
                    'edit_url' => $user->editUrl(),
                    'editable' => User::current()->can('edit', $user),
                    'deleteable' => User::current()->can('delete', $user),
                    'roles' => $user->isSuper() ? ['Super Admin'] : $user->roles()->map->title()->values(),
                    'last_login' => optional($user->lastLogin())->diffForHumans() ?? __("Never"),
                    'actions' => Action::for($user),
                ];
            });

        return Resource::collection($users)->additional(['meta' => [
            'filters' => $request->filters,
            'sortColumn' => $sort,
            'columns' => [
                Column::make('email')->label(__('Email')),
                Column::make('name')->label(__('Name')),
                Column::make('roles')->label(__('Roles')),
                Column::make('last_login')->label(__('Last Login')),
            ],
        ]]);
    }

    protected function filter($query, $filters)
    {
        foreach ($filters as $handle => $values) {
            $class = app('statamic.scopes')->get($handle);
            $filter = app($class);
            $filter->apply($query, $values);
        }
    }

    /**
     * Create a new user
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $this->authorize('create', UserContract::class);

        $blueprint = Blueprint::find('user');

        $fields = $blueprint->fields()->preProcess();

        $viewData = [
            'title' => __('Create'),
            'values' => $fields->values()->all(),
            'meta' => $fields->meta(),
            'blueprint' => $blueprint->toPublishArray(),
            'actions' => [
                'save' => cp_route('users.store'),
            ],
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::users.create', $viewData);
    }

    public function store(Request $request)
    {
        $this->authorize('create', UserContract::class);

        $blueprint = Blueprint::find('user');

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate(['email' => 'required|email|unique_user_value']);

        $values = $fields->process()->values()->except(['email', 'groups', 'roles']);

        $user = User::make()
            ->email($request->email)
            ->data($values)
            ->roles($request->roles ?? [])
            ->groups($request->groups ?? []);

        if ($request->super) {
            $user->makeSuper();
        }

        $user->save();

        if ($request->invitation['send']) {
            ActivateAccount::subject($request->invitation['subject']);
            ActivateAccount::body($request->invitation['message']);
            $user->generateTokenAndSendPasswordResetNotification();
        }

        return array_merge($user->toArray(), [
            'redirect' => $user->editUrl(),
            'activationUrl' => $request->invitation['send'] ? null : $user->getPasswordResetUrl(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        $user = User::find($id);

        $this->authorize('edit', $user);

        $fields = $user->blueprint()
            ->fields()
            ->addValues($user->data()->merge(['email' => $user->email()])->all())
            ->preProcess();

        $viewData = [
            'title' => $user->email(),
            'values' => $fields->values()->all(),
            'meta' => $fields->meta(),
            'blueprint' => $user->blueprint()->toPublishArray(),
            'reference' => $user->reference(),
            'actions' => [
                'save' => $user->updateUrl(),
                'password' => cp_route('users.password.update', $user->id()),
            ],
            'canEditPassword' => User::fromUser($request->user())->can('editPassword', $user)
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::users.edit', $viewData);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        $this->authorize('edit', $user);

        $fields = $user->blueprint()->fields()->addValues($request->all());

        $fields->validate(['email' => 'required|unique_user_value:'.$id]);

        $values = $fields->process()->values()->except(['email', 'groups', 'roles']);

        foreach ($values as $key => $value) {
            $user->set($key, $value);
        }
        $user->email($request->email);

        if ($request->roles) {
            $user->roles($request->roles);
        }

        if ($request->groups) {
            $user->groups($request->groups);
        }

        $user->save();

        return $user->toArray();
    }

    public function destroy($user)
    {
        if (! $user = User::find($user)) {
            return $this->pageNotFound();
        }

        $this->authorize('delete', $user);

        $user->delete();

        return response('', 204);
    }
}
