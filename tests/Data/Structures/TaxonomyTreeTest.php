<?php

namespace Tests\Data\Structures;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\TaxonomyTreeSaving;
use Statamic\Facades\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UnlinksPaths;

class TaxonomyTreeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use UnlinksPaths;

    #[Test]
    public function it_fires_a_saving_event()
    {
        Event::fake();

        $tree = $this->makeTree();
        $tree->save();

        Event::assertDispatched(TaxonomyTreeSaving::class);
    }

    #[Test]
    public function returning_false_in_taxonomy_tree_saving_stops_saving()
    {
        Event::listen(TaxonomyTreeSaving::class, function (TaxonomyTreeSaving $event) {
            return false;
        });

        $tree = $this->makeTree();
        $tree->save();

        $this->assertFileDoesNotExist($tree->path());
    }

    private function makeTree()
    {
        $taxonomy = Taxonomy::make('test')->structureContents([]);
        Taxonomy::shouldReceive('findByHandle')->with('test')->andReturn($taxonomy);

        return $taxonomy->structure()->makeTree('en');
    }
}
