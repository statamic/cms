<?php

namespace Statamic\Providers;

use Statamic\Policies;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Statamic\Contracts\Permissions\Role;
use Statamic\Extensions\FileUserProvider;
use Statamic\Auth\Protect\ProtectorManager;
use Statamic\Contracts\Permissions\UserGroup;
use Facades\Statamic\Permissions\CorePermissions;
use Statamic\Contracts\Permissions\RoleRepository;
use Statamic\Contracts\Permissions\UserGroupRepository;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \Statamic\Contracts\Data\Structures\Structure::class => Policies\StructurePolicy::class,
        \Statamic\Contracts\Data\Entries\Collection::class => Policies\CollectionPolicy::class,
        \Statamic\Contracts\Data\Entries\Entry::class => Policies\EntryPolicy::class,
        \Statamic\Contracts\Data\Globals\GlobalSet::class => Policies\GlobalSetPolicy::class,
    ];

    public function register()
    {
        $this->app->bind(Role::class, config('statamic.users.roles.role'));

        $this->app->singleton(RoleRepository::class, function () {
            return app()
                ->make(config('statamic.users.roles.repository'))
                ->path(config('statamic.users.roles.path'));
        });

        $this->app->singleton(UserGroupRepository::class, function () {
            return app()
                ->make(config('statamic.users.groups.repository'))
                ->path(config('statamic.users.groups.path'));
        });

        $this->app->bind(UserGroup::class, config('statamic.users.groups.group'));

        $this->app->singleton(ProtectorManager::class, function ($app) {
            return new ProtectorManager($app);
        });
    }

    public function boot()
    {
        $this->autoConfigure();

        Auth::provider('file', function () {
            return new FileUserProvider;
        });

        Gate::before(function ($user, $ability) {
            return $user->isSuper() ? true : null;
        });

        CorePermissions::boot();

        foreach ($this->policies as $key => $policy) {
            Gate::policy($key, $policy);
        }
    }

    protected function autoConfigure()
    {
        if (! config('statamic.users.auto_configure')) {
            return;
        }

        config(['auth.providers' => [
            'users' => [
                'driver' => 'file',
            ]
        ]]);
    }
}
