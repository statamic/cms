<?php

namespace Tests\Feature\Navigation;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Nav;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteNavigationTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $nav = $this->createNav();
        $this->assertCount(1, Nav::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->postJson(cp_route('navigation.actions.run'), [
                'selections' => [$nav->handle()],
                'action' => 'delete',
                'values' => [],
            ])
            ->assertForbidden();

        $this->assertCount(1, Nav::all());
    }

    #[Test]
    public function it_deletes_the_navigation()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure navs']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $nav = $this->createNav();
        $this->assertCount(1, Nav::all());

        $this
            ->actingAs($user)
            ->postJson(cp_route('navigation.actions.run'), [
                'selections' => [$nav->handle()],
                'action' => 'delete',
                'values' => [],
            ])
            ->assertOk();

        $this->assertCount(0, Nav::all());
    }

    private function createNav()
    {
        return Nav::make('test')
            ->title('Existing')
            ->maxDepth(1)
            ->expectsRoot(false)
            ->tap(function ($nav) {
                $nav->makeTree('en')->save();
                $nav->save();
            });
    }
}
