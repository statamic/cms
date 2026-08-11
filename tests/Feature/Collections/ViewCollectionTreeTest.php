<?php

namespace Tests\Feature\Collections;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewCollectionTreeTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_tree()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = $this->createStructuredCollection();

        $response = $this
            ->actingAs($user)
            ->index($collection)
            ->assertOk();

        $this->assertEquals(['e1', 'e2'], collect($response->json('pages'))->map->entry->all());
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission_to_view_the_collection()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = $this->createStructuredCollection();

        $this
            ->actingAs($user)
            ->index($collection)
            ->assertForbidden();
    }

    #[Test]
    public function it_shows_the_tree_for_a_site_you_can_access()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'locale' => 'fr_FR'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'view test entries', 'access en site', 'access fr site']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = $this->createStructuredCollection(['en', 'fr']);

        $response = $this
            ->actingAs($user)
            ->index($collection, 'fr')
            ->assertOk();

        $this->assertEquals(['fr1'], collect($response->json('pages'))->map->entry->all());
    }

    #[Test]
    public function it_denies_access_to_a_site_you_cannot_access()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'locale' => 'fr_FR'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'view test entries', 'access en site']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = $this->createStructuredCollection(['en', 'fr']);

        $this
            ->actingAs($user)
            ->index($collection, 'fr')
            ->assertForbidden();
    }

    #[Test]
    public function configure_collections_can_view_any_site()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US'],
            'fr' => ['url' => '/fr', 'locale' => 'fr_FR'],
        ]);

        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = $this->createStructuredCollection(['en', 'fr']);

        $response = $this
            ->actingAs($user)
            ->index($collection, 'fr')
            ->assertOk();

        $this->assertEquals(['fr1'], collect($response->json('pages'))->map->entry->all());
    }

    #[Test]
    public function it_404s_when_the_site_doesnt_exist()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = $this->createStructuredCollection();

        $this
            ->actingAs($user)
            ->index($collection, 'nope')
            ->assertNotFound();
    }

    private function createStructuredCollection($sites = ['en'])
    {
        $collection = tap(Collection::make('test')->sites($sites)->routes('{parent_uri}/{slug}'))->save();

        EntryFactory::id('e1')->collection($collection)->locale('en')->slug('a')->create();
        EntryFactory::id('e2')->collection($collection)->locale('en')->slug('b')->create();

        $collection->structureContents(['root' => false])->save();

        $collection->structure()->in('en')->tree([
            ['entry' => 'e1'],
            ['entry' => 'e2'],
        ])->save();

        if (in_array('fr', $sites)) {
            EntryFactory::id('fr1')->collection($collection)->locale('fr')->slug('c')->create();

            $collection->structure()->in('fr')->tree([
                ['entry' => 'fr1'],
            ])->save();
        }

        return $collection;
    }

    private function index($collection, $site = null)
    {
        $url = cp_route('collections.tree.index', $collection->handle());

        if ($site) {
            $url .= '?site='.$site;
        }

        return $this->getJson($url);
    }
}
