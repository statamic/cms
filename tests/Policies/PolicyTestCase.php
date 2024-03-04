<?php

namespace Tests\Policies;

use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class PolicyTestCase extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    protected function withSites(array $sites)
    {
        Site::setConfig(['sites' => collect($sites)->mapWithKeys(fn ($site) => [
            $site => ['locale' => $site, 'url' => '/'],
        ])]);
    }

    protected function userWithPermissions(array $permissions)
    {
        $role = str_random();

        $this->setTestRole($role, $permissions);

        return tap(User::make()->assignRole($role))->save();
    }
}
