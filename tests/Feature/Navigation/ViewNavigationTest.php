<?php

namespace Tests\Feature\Navigation;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewNavigationTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_provides_the_collection_tree_when_you_can_view_the_collection()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test nav', 'view pages entries']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $nav = $this->createNavLinkedToStructuredCollection();

        $this
            ->actingAs($user)
            ->visitShow($nav)
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('navigation/Show')
                ->where('collectionTree.title', 'Pages')
            );
    }

    #[Test]
    public function it_doesnt_provide_the_collection_tree_when_you_cant_view_the_collection()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test nav']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();
        $nav = $this->createNavLinkedToStructuredCollection();

        $this
            ->actingAs($user)
            ->visitShow($nav)
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('navigation/Show')
                ->where('collectionTree', null)
            );
    }

    private function createNavLinkedToStructuredCollection()
    {
        $collection = tap(Facades\Collection::make('pages')->title('Pages')->routes('{parent_uri}/{slug}'))->save();

        EntryFactory::id('e1')->collection($collection)->slug('a')->create();

        $collection->structureContents(['root' => false])->save();
        $collection->structure()->in('en')->tree([['entry' => 'e1']])->save();

        $nav = tap(Facades\Nav::make('test')->collections(['pages']))->save();
        $nav->makeTree('en', [['id' => 'id1', 'title' => 'One', 'url' => 'http://example.com/one']])->save();

        return $nav;
    }

    private function visitShow($nav)
    {
        return $this->get(cp_route('navigation.show', $nav->handle()));
    }
}
