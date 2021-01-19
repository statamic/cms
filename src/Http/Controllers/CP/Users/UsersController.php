<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\CP\Column;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Http\Resources\CP\Users\Users;
use Statamic\Notifications\ActivateAccount;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Statamic;

class UsersController extends CpController
{
    use QueriesFilters;

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

        $activeFilterBadges = $this->queryFilters($query, $request->filters);

        $users = $query
            ->orderBy($sort = request('sort', 'email'), request('order', 'asc'))
            ->paginate(request('perPage'));

        return (new Users($users))
            ->blueprint(User::blueprint())
            ->columns(collect([
                Column::make('email')->label(__('Email')),
                Column::make('name')->label(__('Name')),
                Statamic::pro() ? Column::make('roles')->label(__('Roles'))->fieldtype('relationship')->sortable(false) : null,
                Column::make('last_login')->label(__('Last Login'))->sortable(false),
            ])->filter()->values()->all())
            ->additional(['meta' => [
                'activeFilterBadges' => $activeFilterBadges,
            ]]);
    }

    /**
     * Create a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $this->authorizePro();
        $this->authorize('create', UserContract::class);

        $blueprint = User::blueprint();

        $fields = $blueprint->fields()->preProcess();

        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_ACTIVATIONS);
        $expiry = config("auth.passwords.{$broker}.expire") / 60;

        $viewData = [
            'title' => __('Create'),
            'values' => $fields->values()->all(),
            'meta' => $fields->meta(),
            'blueprint' => $blueprint->toPublishArray(),
            'actions' => [
                'save' => cp_route('users.store'),
            ],
            'expiry' => $expiry,
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::users.create', $viewData);
    }

    public function store(Request $request)
    {
        $this->authorizePro();
        $this->authorize('create', UserContract::class);

        $blueprint = User::blueprint();

        $fields = $blueprint->fields()->only(['email', 'name'])->addValues($request->all());

        $fields->validate(['email' => 'required|email|unique_user_value']);

        $values = $fields->process()->values()->except(['email', 'groups', 'roles']);

        $user = User::make()
            ->email($request->email)
            ->data($values);

        if ($request->roles && User::current()->can('edit roles')) {
            $user->roles($request->roles);
        }

        if ($request->groups && User::current()->can('edit user groups')) {
            $user->groups($request->groups);
        }

        if ($request->super && User::current()->can('edit roles')) {
            $user->makeSuper();
        }

        $user->save();

        if ($request->invitation['send']) {
            ActivateAccount::subject($request->invitation['subject']);
            ActivateAccount::body($request->invitation['message']);
            $user->generateTokenAndSendActivateAccountNotification();
            $url = null;
        } else {
            $url = PasswordReset::url($user->generateActivateAccountToken(), PasswordReset::BROKER_ACTIVATIONS);
        }

        return [
            'redirect' => $user->editUrl(),
            'activationUrl' => $url,
        ];
    }

    public function edit(Request $request, $user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        $this->authorize('edit', $user);

        $blueprint = $user->blueprint();

        if (! User::current()->can('edit roles')) {
            $blueprint->ensureField('roles', ['read_only' => true]);
        }

        if (! User::current()->can('edit user groups')) {
            $blueprint->ensureField('groups', ['read_only' => true]);
        }

        $fields = $blueprint
            ->removeField('password')
            ->removeField('password_confirmation')
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
                'editBlueprint' => cp_route('users.blueprint.edit'),
            ],
            'canEditPassword' => User::fromUser($request->user())->can('editPassword', $user),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::users.edit', $viewData);
    }

    public function update(Request $request, $user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        $this->authorize('edit', $user);

        $fields = $user->blueprint()->fields()->except(['password'])->addValues($request->all());

        $fields->validate(['email' => 'required|unique_user_value:'.$user->id()]);

        $values = $fields->process()->values()->except(['email', 'groups', 'roles']);

        foreach ($values as $key => $value) {
            $user->set($key, $value);
        }
        $user->email($request->email);

        if (User::current()->can('edit roles')) {
            $user->roles($request->roles);
        }

        if (User::current()->can('edit user groups')) {
            $user->groups($request->groups);
        }

        $user->save();

        return ['title' => $user->title()];
    }

    public function destroy($user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (! $user = User::find($user)) {
            return $this->pageNotFound();
        }

        $this->authorize('delete', $user);

        $user->delete();

        return response('', 204);
    }
}
