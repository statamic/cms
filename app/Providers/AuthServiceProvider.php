<?php

namespace Statamic\Providers;

use Statamic\Config\Roles;
use Illuminate\Support\Facades\Auth;
use Statamic\Permissions\Permissions;
use Illuminate\Support\ServiceProvider;
use Statamic\Extensions\FileUserProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('permissions', function () {
            return new Permissions;
        });

        $this->app->alias('permissions', 'Statamic\Permissions\Permissions');

        $this->app->singleton('Statamic\Config\Roles', function () {
            return collect();
        });
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(GateContract $gate, Permissions $permissions)
    {
        Auth::provider('file', function () {
            return new FileUserProvider;
        });

        $this->loadRoles();

        $permissions->build();

        foreach ($permissions->all(true) as $group => $permission) {
            $gate->define($permission, function ($user) use ($permission) {
                return $user->isSuper() || $user->hasPermission($permission);
            });
        }
    }

    /**
     * Load user roles
     */
    public function loadRoles()
    {
        $roles = $this->app->make('Statamic\Config\Roles');

        foreach (config('auth.roles') as $id => $data) {
            $roles[$id] = app('Statamic\Contracts\Permissions\RoleFactory')->create($data, $id);
        }
    }
}
