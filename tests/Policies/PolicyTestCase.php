<?php

namespace Tests\Policies;

use Statamic\Facades\User;
use Statamic\Support\Str;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class PolicyTestCase extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    protected function withSites(array $sites)
    {
        $this->setSites(collect($sites)->mapWithKeys(fn ($site) => [
            $site => ['locale' => $site, 'url' => '/'],
        ]));
    }

    protected function userWithPermissions(array $permissions)
    {
        $role = Str::random();

        $this->setTestRole($role, $permissions);

        return tap(User::make()->assignRole($role))->save();
    }
}
