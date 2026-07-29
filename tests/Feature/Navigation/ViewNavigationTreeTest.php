<?php

namespace Tests\Feature\Navigation;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Nav;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewNavigationTreeTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_tree()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test nav']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $nav = $this->createNavWithTree();

        $response = $this
            ->actingAs($user)
            ->index($nav)
            ->assertOk();

        $this->assertEquals(['One', 'Two'], collect($response->json('pages'))->map->title->all());
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission_to_view_the_nav()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $nav = $this->createNavWithTree();

        $this
            ->actingAs($user)
            ->index($nav)
            ->assertForbidden();
    }

    private function createNavWithTree()
    {
        $nav = tap(Nav::make('test'))->save();

        $nav->makeTree('en', [
            ['id' => 'id1', 'title' => 'One', 'url' => 'http://example.com/one'],
            ['id' => 'id2', 'title' => 'Two', 'url' => 'http://example.com/two'],
        ])->save();

        return $nav;
    }

    private function index($nav)
    {
        return $this->getJson(cp_route('navigation.tree.index', $nav->handle()));
    }
}
