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

        // TODO: Implement correctly.

        // $this->loadRoles();

        // $permissions->build();
        // $perms = $permissions->all(true);

        $perms = [
            "super",
            "cp:access",
            "content:view_drafts_on_frontend",
            "pages:view",
            "pages:edit",
            "pages:create",
            "pages:delete",
            "pages:reorder",
            "forms",
            "updater",
            "updater:update",
            "importer",
            "users:view",
            "users:edit",
            "users:edit-passwords",
            "users:edit-roles",
            "users:create",
            "users:delete",
            "resolve_duplicates",
            "collections:*:view",
            "collections:*:edit",
            "collections:blog:view",
            "collections:blog:edit",
            "collections:blog:create",
            "collections:blog:delete",
            "collections:things:view",
            "collections:things:edit",
            "collections:things:create",
            "collections:things:delete",
            "taxonomies:*:view",
            "taxonomies:*:edit",
            "taxonomies:tags:view",
            "taxonomies:tags:edit",
            "taxonomies:tags:create",
            "taxonomies:tags:delete",
            "globals:*:view",
            "globals:*:edit",
            "globals:global:view",
            "globals:global:edit",
            "assets:*:view",
            "assets:*:edit",
            "assets:main:view",
            "assets:main:edit",
            "assets:main:create",
            "assets:main:delete",
        ];


        foreach ($perms as $group => $permission) {
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

        foreach (config('statamic.users.roles') as $id => $data) {
            $roles[$id] = app('Statamic\Contracts\Permissions\RoleFactory')->create($data, $id);
        }
    }
}
