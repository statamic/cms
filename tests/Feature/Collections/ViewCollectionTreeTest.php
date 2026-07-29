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

    private function createStructuredCollection()
    {
        $collection = tap(Collection::make('test')->routes('{parent_uri}/{slug}'))->save();

        EntryFactory::id('e1')->collection($collection)->slug('a')->create();
        EntryFactory::id('e2')->collection($collection)->slug('b')->create();

        $collection->structureContents(['root' => false])->save();
        $collection->structure()->in('en')->tree([
            ['entry' => 'e1'],
            ['entry' => 'e2'],
        ])->save();

        return $collection;
    }

    private function index($collection)
    {
        return $this->getJson(cp_route('collections.tree.index', $collection->handle()));
    }
}
