<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Statamic\Contracts\Permissions\Role;
use Statamic\Extensions\FileUserProvider;
use Statamic\Contracts\Permissions\UserGroup;
use Statamic\Contracts\Permissions\RoleRepository;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Role::class, config('statamic.users.roles.role'));

        $this->app->singleton(RoleRepository::class, function () {
            return app()
                ->make(config('statamic.users.roles.repository'))
                ->path(config('statamic.users.roles.path'));
        });

        $this->app->bind(UserGroup::class, config('statamic.users.groups.group'));
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
