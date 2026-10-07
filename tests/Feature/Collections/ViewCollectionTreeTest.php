<?php

namespace Tests\Feature\Collections;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewCollectionTreeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_includes_actions_for_the_root_page()
    {
        $collection = $this->createCollectionWithTree(expectsRoot: true);

        $response = $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->getJson(cp_route('collections.tree.index', $collection->handle()))
            ->assertOk()
            ->assertJsonMissingPath('pages.1.actions');

        $this->assertContains('duplicate_entry', collect($response->json('pages.0.actions'))->pluck('handle'));
    }

    #[Test]
    public function it_doesnt_include_actions_when_the_structure_doesnt_expect_a_root()
    {
        $collection = $this->createCollectionWithTree(expectsRoot: false);

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->getJson(cp_route('collections.tree.index', $collection->handle()))
            ->assertOk()
            ->assertJsonMissingPath('pages.0.actions')
            ->assertJsonMissingPath('pages.1.actions');
    }

    private function createCollectionWithTree(bool $expectsRoot)
    {
        $collection = tap(Collection::make('pages')->routes('{parent_uri}/{slug}'))->save();
        EntryFactory::id('home')->collection($collection)->slug('home')->create();
        EntryFactory::id('about')->collection($collection)->slug('about')->create();
        $collection->structureContents(['root' => $expectsRoot])->save();
        $collection->structure()->in('en')->tree([
            ['entry' => 'home'],
            ['entry' => 'about'],
        ])->save();

        return $collection;
    }
}
