<?php

namespace Tests;

use Illuminate\Support\Collection;
use Statamic\Contracts\Permissions\Role;
use Statamic\Permissions\RoleRepository;
use Statamic\Contracts\Permissions\RoleRepository as RepositoryContract;

trait FakesRoles
{
    private function setTestRoles($roles)
    {
        $roles = collect($roles)->map(function ($permissions, $handle) {
            return app(Role::class)
                ->handle($handle)
                ->addPermission($permissions);
        });

        $fake = new class($roles) extends RoleRepository {
            protected $roles;
            public function __construct($roles) {
                $this->roles = $roles;
            }
            public function all(): Collection {
                return $this->roles;
            }
        };

        app()->instance(RepositoryContract::class, $fake);
    }
}
