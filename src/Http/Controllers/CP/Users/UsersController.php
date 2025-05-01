<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Auth\Passwords\PasswordReset;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Action;
use Statamic\Facades\CP\Toast;
use Statamic\Facades\Scope;
use Statamic\Facades\Search;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Http\Resources\CP\Users\Users;
use Statamic\Notifications\ActivateAccount;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Rules\UniqueUserValue;
use Statamic\Search\Result;
use Symfony\Component\Mailer\Exception\TransportException;

class UsersController extends CpController
{
    use ExtractsFromUserFields,
        QueriesFilters;

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

    protected function indexQuery()
    {
        $query = User::query();

        if ($search = request('search')) {
            $query = $this->searchUsers($search, $query);
        }

        return $query;
    }

    protected function json($request)
    {
        $query = $request->group
            ? UserGroup::find($request->group)->queryUsers()
            : User::query();

        if ($search = request('search')) {
            $query = $this->searchUsers($search, $query, ! $request->has('group'));
        }

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'blueprints' => ['user'],
        ]);

        $sortField = request('sort');
        $sortDirection = request('order', 'asc');

        if (! $sortField && ! request('search')) {
            $sortField = config('statamic.user.sort_field', 'email');
            $sortDirection = config('statamic.user.sort_direction', 'asc');
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $users = $query->paginate(request('perPage'));

        if ($users->getCollection()->first() instanceof Result) {
            $users->setCollection($users->getCollection()->map->getSearchable());
        }

        return (new Users($users))
            ->blueprint(User::blueprint())
            ->columnPreferenceKey('users.columns')
            ->additional(['meta' => [
                'activeFilterBadges' => $activeFilterBadges,
            ]]);
    }

    protected function searchUsers($search, $query, $useIndex = true)
    {
        if ($useIndex && Search::indexes()->has('users')) {
            return Search::index('users')->ensureExists()->search($search);
        }

        $query->where(function ($query) use ($search) {
            $query
                ->where('email', 'like', '%'.$search.'%')
                ->when(User::blueprint()->hasField('first_name'), function ($query) use ($search) {
                    foreach (explode(' ', $search) as $word) {
                        $query
                            ->orWhere('first_name', 'like', '%'.$word.'%')
                            ->orWhere('last_name', 'like', '%'.$word.'%');
                    }
                }, function ($query) use ($search) {
                    $query->orWhere('name', 'like', '%'.$search.'%');
                });
        });

        return $query;
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
        $blueprint->ensureFieldHasConfig('email', ['validate' => 'required']);

        $fields = $blueprint->fields()->preProcess();

        $broker = config('statamic.users.passwords.'.PasswordReset::BROKER_ACTIVATIONS);
        $expiry = config("auth.passwords.{$broker}.expire") / 60;

        $additional = $fields->all()
            ->reject(fn ($field) => in_array($field->handle(), ['roles', 'groups', 'super']))
            ->keys();

        $viewData = [
            'values' => (object) $fields->values()->only($additional)->all(),
            'meta' => (object) $fields->meta()->only($additional)->all(),
            'fields' => collect($blueprint->fields()->toPublishArray())->filter(fn ($field) => $additional->contains($field['handle']))->values()->all(),
            'blueprint' => $blueprint->toPublishArray(),
            'expiry' => $expiry,
            'separateNameFields' => $blueprint->hasField('first_name'),
            'canSendInvitation' => config('statamic.users.wizard_invitation'),
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

        $fields = $blueprint->fields()->except(['roles', 'groups'])->addValues($request->all());

        $fields->validate(['email' => ['required', 'email', new UniqueUserValue]]);

        if ($request->input('_validate_only')) {
            return [];
        }

        $values = $fields->process()->values()->except(['email']);

        $user = User::make()
            ->email($request->email)
            ->data($values);

        if ($request->roles && User::current()->can('assign roles')) {
            $user->explicitRoles($request->roles);
        }

        if ($request->groups && User::current()->can('assign user groups')) {
            $user->groups($request->groups);
        }

        if ($request->super && User::current()->isSuper()) {
            $user->makeSuper();
        }

        $user->save();

        PasswordReset::redirectAfterReset(cp_route('index'));

        if ($request->invitation['send']) {
            ActivateAccount::subject($request->invitation['subject']);
            ActivateAccount::body($request->invitation['message']);

            try {
                $user->generateTokenAndSendActivateAccountNotification();
            } catch (TransportException $e) {
                Toast::error(__('statamic::messages.user_activation_email_not_sent_error'));
            }

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

        if (! User::current()->can('assign roles')) {
            $blueprint->ensureField('roles', ['visibility' => 'read_only']);
        }

        if (! User::current()->can('assign user groups')) {
            $blueprint->ensureField('groups', ['visibility' => 'read_only']);
        }

        if (User::current()->isSuper() && User::current()->id() !== $user->id()) {
            $blueprint->ensureField('super', ['type' => 'toggle', 'display' => __('permissions.super')]);
        }

        [$values, $meta] = $this->extractFromFields($user, $blueprint);

        $viewData = [
            'title' => $user->email(),
            'values' => array_merge($values, ['id' => $user->id()]),
            'meta' => $meta,
            'blueprint' => $user->blueprint()->toPublishArray(),
            'reference' => $user->reference(),
            'actions' => [
                'save' => $user->updateUrl(),
                'password' => cp_route('users.password.update', $user->id()),
                'editBlueprint' => cp_route('users.blueprint.edit'),
            ],
            'canEditPassword' => User::fromUser($request->user())->can('editPassword', $user),
            'requiresCurrentPassword' => $isCurrentUser = $request->user()->id === $user->id(),
            'itemActions' => Action::for($user, ['view' => 'form']),
            'twoFactor' => $isCurrentUser ? [
                'isEnforced' => $user->isTwoFactorAuthenticationRequired(),
                'wasSetup' => $user->hasEnabledTwoFactorAuthentication(),
                'routes' => [
                    'enable' => cp_route('users.two-factor.enable'),
                    'disable' => cp_route('users.two-factor.disable'),
                    'recoveryCodes' => [
                        'show' => cp_route('users.two-factor.recovery-codes.show'),
                        'generate' => cp_route('users.two-factor.recovery-codes.generate'),
                        'download' => cp_route('users.two-factor.recovery-codes.download'),
                    ],
                ],
            ] : null,
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

        $fields = $user->blueprint()->fields()->except(['password'])->addValues($request->except('id'));

        $fields
            ->validator()
            ->withRules(['email' => ['required', 'email', new UniqueUserValue(except: $user->id())]])
            ->withReplacements(['id' => $user->id()])
            ->validate();

        $values = $fields->process()->values()->except(['email', 'groups', 'roles', 'super']);

        foreach ($values as $key => $value) {
            $user->set($key, $value);
        }

        if (User::current()->isSuper() && User::current()->id() !== $user->id()) {
            $user->super = $request->super;
        }

        $user->email($request->email);

        if (User::current()->can('assign roles')) {
            $user->explicitRoles($request->roles);
        }

        if (User::current()->can('assign user groups')) {
            $user->groups($request->groups);
        }

        $save = $user->save();

        [$values] = $this->extractFromFields($user, $user->blueprint());

        return [
            'title' => $user->title(),
            'saved' => is_bool($save) ? $save : true,
            'data' => [
                'values' => $values,
            ],
        ];
    }
}
