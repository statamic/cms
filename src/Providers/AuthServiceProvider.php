<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Statamic\Auth\Passwords\PasswordBrokerManager;
use Statamic\Auth\PermissionCache;
use Statamic\Auth\Permissions;
use Statamic\Auth\Protect\ProtectorManager;
use Statamic\Auth\UserProvider;
use Statamic\Auth\UserRepositoryManager;
use Statamic\Contracts\Auth\RoleRepository;
use Statamic\Contracts\Auth\UserGroupRepository;
use Statamic\Contracts\Auth\UserRepository;
use Statamic\Facades\User;
use Statamic\Policies;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \Statamic\Contracts\Structures\Nav::class => Policies\NavPolicy::class,
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
    ];

    public function register()
    {
        $this->app->singleton(UserRepositoryManager::class, function ($app) {
            return new UserRepositoryManager($app);
        });

        $this->app->singleton(UserRepository::class, function ($app) {
            $repository = $app[UserRepositoryManager::class]->repository();

            foreach ($repository::bindings() as $abstract => $concrete) {
                if (! $this->app->bound($abstract)) {
                    $this->app->bind($abstract, $concrete);
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
    }

    public function boot()
    {
        Auth::provider('statamic', function () {
            return new UserProvider;
        });

        Gate::before(function ($user, $ability) {
            return optional(User::fromUser($user))->isSuper() ? true : null;
        });

        Gate::after(function ($user, $ability) {
            return optional(User::fromUser($user))->hasPermission($ability) === true ? true : null;
        });

        foreach ($this->policies as $key => $policy) {
            Gate::policy($key, $policy);
        }

        $this->app->extend('auth.password', function ($broker, $app) {
            return ($app['auth']->getProvider() instanceof UserProvider)
                ? new PasswordBrokerManager($app)
                : $broker;
        });
    }
}
