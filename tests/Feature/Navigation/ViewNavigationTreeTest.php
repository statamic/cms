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

    #[Test]
    public function it_shows_the_tree_for_a_site_you_can_access()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'locale' => 'fr_FR'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'view test nav', 'access en site', 'access fr site']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $nav = $this->createNavWithTree();

        $response = $this
            ->actingAs($user)
            ->index($nav, 'fr')
            ->assertOk();

        $this->assertEquals(['Un'], collect($response->json('pages'))->map->title->all());
    }

    #[Test]
    public function it_denies_access_to_a_site_you_cannot_access()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'locale' => 'fr_FR'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'view test nav', 'access en site']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $nav = $this->createNavWithTree();

        $this
            ->actingAs($user)
            ->index($nav, 'fr')
            ->assertForbidden();
    }

    #[Test]
    public function it_404s_when_the_nav_doesnt_exist()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test nav']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $this->createNavWithTree();

        $this
            ->actingAs($user)
            ->getJson(cp_route('navigation.tree.index', 'nope'))
            ->assertNotFound();
    }

    #[Test]
    public function it_404s_when_the_nav_has_no_tree_for_the_site()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'locale' => 'fr_FR'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'view test nav', 'access en site', 'access fr site']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en', [['id' => 'id1', 'title' => 'One', 'url' => 'http://example.com/one']])->save();

        $this
            ->actingAs($user)
            ->index($nav, 'fr')
            ->assertNotFound();
    }

    #[Test]
    public function super_users_get_a_404_rather_than_a_403_when_the_nav_has_no_tree_for_the_site()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'locale' => 'fr_FR'],
        ]);

        $user = tap(User::make()->makeSuper())->save();

        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en', [['id' => 'id1', 'title' => 'One', 'url' => 'http://example.com/one']])->save();

        $this
            ->actingAs($user)
            ->index($nav, 'fr')
            ->assertNotFound();
    }

    private function createNavWithTree()
    {
        $nav = tap(Nav::make('test'))->save();

        $nav->makeTree('en', [
            ['id' => 'id1', 'title' => 'One', 'url' => 'http://example.com/one'],
            ['id' => 'id2', 'title' => 'Two', 'url' => 'http://example.com/two'],
        ])->save();

        $nav->makeTree('fr', [
            ['id' => 'id3', 'title' => 'Un', 'url' => 'http://example.com/un'],
        ])->save();

        return $nav;
    }

    private function index($nav, $site = null)
    {
        $url = cp_route('navigation.tree.index', $nav->handle());

        if ($site) {
            $url .= '?site='.$site;
        }

        return $this->getJson($url);
    }
}
