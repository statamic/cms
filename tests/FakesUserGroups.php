<?php

namespace Tests;

use Illuminate\Support\Collection;
use Statamic\Auth\File\UserGroup as FileUserGroup;
use Statamic\Auth\File\UserGroupRepository;
use Statamic\Contracts\Auth\UserGroupRepository as RepositoryContract;
use Statamic\Facades\UserGroup;

trait FakesUserGroups
{
    private function setTestUserGroups($groups)
    {
        $groups = collect($groups)
            ->mapWithKeys(function ($roles, $handle) {
                $handle = is_string($roles) ? $roles : $handle;
                $roles = is_string($roles) ? [] : $roles;

                return [$handle => $roles];
            })
            ->map(function ($roles, $handle) {
                return $roles instanceof FileUserGroup
                    ? $roles->handle($handle)
                    : UserGroup::make()->handle($handle)->roles($roles);
            });

        $fake = new class($groups) extends UserGroupRepository
        {
            protected $groups;

            public function __construct($groups)
            {
                $this->groups = $groups;
            }

            public function all(): Collection
            {
                return $this->groups;
            }
        };

        app()->instance(RepositoryContract::class, $fake);
        UserGroup::swap($fake);
    }
}
