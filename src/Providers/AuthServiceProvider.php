<?php

namespace Statamic\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Statamic\Auth\Passwords\PasswordBrokerManager;
use Statamic\Auth\PermissionCache;
use Statamic\Auth\Permissions;
use Statamic\Auth\Protect\ProtectorManager;
use Statamic\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Auth\UserProvider;
use Statamic\Auth\UserRepositoryManager;
use Statamic\Contracts\Auth\RoleRepository;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Statamic\Contracts\Auth\UserGroupRepository;
use Statamic\Contracts\Auth\UserRepository;
use Statamic\Facades\Permission;
use Statamic\Facades\User;
use Statamic\Policies;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \Statamic\Contracts\Structures\Nav::class => Policies\NavPolicy::class,
        \Statamic\Contracts\Structures\NavTree::class => Policies\NavTreePolicy::class,
        \Statamic\Contracts\Entries\Collection::class => Policies\CollectionPolicy::class,
        \Statamic\Contracts\Entries\Entry::class => Policies\EntryPolicy::class,
        \Statamic\Contracts\Taxonomies\Taxonomy::class => Policies\TaxonomyPolicy::class,
        \Statamic\Contracts\Taxonomies\Term::class => Policies\TermPolicy::class,
        \Statamic\Contracts\Globals\GlobalSet::class => Policies\GlobalSetPolicy::class,
        \Statamic\Contracts\Globals\Variables::class => Policies\GlobalSetVariablesPolicy::class,
        \Statamic\Contracts\Auth\User::class => Policies\UserPolicy::class,
        \Statamic\Contracts\Forms\Form::class => Policies\FormPolicy::class,
        \Statamic\Contracts\Forms\Submission::class => Policies\FormSubmissionPolicy::class,
        \Statamic\Contracts\Assets\Asset::class => Policies\AssetPolicy::class,
        \Statamic\Contracts\Assets\AssetFolder::class => Policies\AssetFolderPolicy::class,
        \Statamic\Contracts\Assets\AssetContainer::class => Policies\AssetContainerPolicy::class,
        \Statamic\Fields\Fieldset::class => Policies\FieldsetPolicy::class,
        \Statamic\Sites\Site::class => Policies\SitePolicy::class,
    ];

    public function register()
    {
        $this->app->singleton(UserRepositoryManager::class, function ($app) {
            return new UserRepositoryManager($app);
        });

        $this->app->singleton(UserRepository::class, function ($app) {
            $repository = $app[UserRepositoryManager::class]->repository();

            foreach ($repository::bindings() as $abstract => $concrete) {
                if (! $app->bound($abstract)) {
                    $app->bind($abstract, $concrete);
                }
            }

            return $repository;
        });

        $this->app->singleton(RoleRepository::class, function ($app) {
            return $app[UserRepository::class]->roleRepository();
        });

        $this->app->singleton(UserGroupRepository::class, function ($app) {
            return $app[UserRepository::class]->userGroupRepository();
        });

        $this->app->singleton(ProtectorManager::class, function ($app) {
            return new ProtectorManager($app);
        });

        $this->app->singleton(Permissions::class, function () {
            return new Permissions;
        });

        $this->app->singleton(PermissionCache::class, function ($app) {
            return new PermissionCache;
        });

        $this->app->singleton(TwoFactorAuthenticationProviderContract::class, TwoFactorAuthenticationProvider::class);
    }

    public function boot()
    {
        Auth::provider('statamic', function () {
            return new UserProvider;
        });

        Gate::after(function ($user, $ability) {
            // If the ability isn't a Statamic permission, we don't want to get involved. 🙈
            if (! Permission::boot()->flattened()->map->value()->contains($ability)) {
                return null;
            }

            $user = User::fromUser($user);

            if ($user->isSuper()) {
                return true;
            }

            if ($user->hasPermission($ability)) {
                return true;
            }
        });

        foreach ($this->policies as $key => $policy) {
            Gate::policy($key, $policy);
        }

        $this->app->extend('auth.password', function ($broker, $app) {
            return ($app['auth']->getProvider() instanceof UserProvider)
                ? new PasswordBrokerManager($app)
                : $broker;
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('send-elevated-session-code', function () {
            return Limit::perMinute(1)->response(function (Request $request) {
                $message = trans_choice('statamic::messages.try_again_in_minutes', 1);

                return $request->wantsJson() ? response(['error' => $message], 429) : back()->with('error', $message);
            });
        });
    }
}
