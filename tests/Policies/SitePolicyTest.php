<?php

namespace Tests\Policies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SitePolicyTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    #[Test]
    public function site_is_viewable_with_permission()
    {
        $this->setSites([
            'first' => ['name' => 'First', 'locale' => 'en_US', 'url' => '/'],
            'second' => ['name' => 'Second', 'locale' => 'en_US', 'url' => '/'],
        ]);

        $this->setTestRoles(['test' => [
            'access second site',
        ]]);

        $user = tap(User::make()->assignRole('test'))->save();

        $this->assertTrue(Site::hasMultiple());
        $this->assertFalse($user->can('view', Site::get('first')));
        $this->assertTrue($user->can('view', Site::get('second')));
    }

    #[Test]
    public function site_is_viewable_without_permission_if_theres_a_single_site()
    {
        $this->setSites([
            'default' => ['name' => 'Default', 'locale' => 'en_US', 'url' => '/'],
        ]);

        $this->setTestRoles(['test' => [
            //
        ]]);

        $user = tap(User::make()->assignRole('test'))->save();

        $this->assertFalse(Site::hasMultiple());
        $this->assertTrue($user->can('view', Site::default()));
    }
}
