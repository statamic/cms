<?php

namespace Tests\Data\Structures;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\CollectionTreeSaving;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Structures\CollectionTree;
use Statamic\Structures\CollectionTreeDiff;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UnlinksPaths;

class CollectionTreeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use UnlinksPaths;

    #[Test]
    public function it_can_get_and_set_the_handle()
    {
        $tree = new CollectionTree;
        $this->assertNull($tree->handle());

        $return = $tree->handle('test');

        $this->assertSame($tree, $return);
        $this->assertEquals('test', $tree->handle());
    }

    #[Test]
    public function it_gets_the_structure()
    {
        $collection = Collection::make('test')->structureContents(['root' => true]);
        $structure = $collection->structure();
        Collection::shouldReceive('findByHandle')->with('test')->once()->andReturn($collection);

        $this->assertNull(Blink::get($blinkKey = 'collection-tree-structure-test'));

        $tree = (new CollectionTree)->handle('test');

        // Do it twice combined with the once() in the mock to show blink works.
        $this->assertSame($structure, $tree->structure());
        $this->assertSame($structure, $tree->structure());
        $this->assertSame($structure, Blink::get($blinkKey));
    }

    #[Test]
    public function it_gets_the_path()
    {
        $collection = Collection::make('pages')->structureContents(['root' => true]);
        Collection::shouldReceive('findByHandle')->with('pages')->andReturn($collection);
        $tree = $collection->structure()->makeTree('en');
        $this->assertEquals($this->fakeStacheDirectory.'/content/structures/collections/pages.yaml', $tree->path());
    }

    #[Test]
    public function it_gets_the_path_when_using_multisite()
    {
        $this->setSites([
            'one' => ['locale' => 'en_US', 'url' => '/one'],
            'two' => ['locale' => 'fr_Fr', 'url' => '/two'],
        ]);

        $collection = Collection::make('pages')->structureContents(['root' => true]);
        Collection::shouldReceive('findByHandle')->with('pages')->andReturn($collection);
        $tree = $collection->structure()->makeTree('en');
        $this->assertEquals($this->fakeStacheDirectory.'/content/structures/collections/en/pages.yaml', $tree->path());
    }

    #[Test]
    public function it_does_a_diff()
    {
        $collection = Collection::make('pages')->structureContents(['root' => true]);
        Collection::shouldReceive('findByHandle')->with('pages')->andReturn($collection);

        $tree = $collection->structure()->makeTree('en', [
            ['entry' => '1.0', 'children' => [
                ['entry' => '1.1'],
                ['entry' => '1.2'],
                ['entry' => '1.3'],
            ]],
            ['entry' => '2.0', 'children' => [
                ['entry' => '2.1'],
                ['entry' => '2.2'],
                ['entry' => '2.3'],
            ]],
        ]);

        $tree->tree([
            ['entry' => '1.0', 'children' => [
                ['entry' => '1.4'],
                ['entry' => '1.2'],
            ]],
            ['entry' => '2.0', 'children' => [
                ['entry' => '2.1'],
                ['entry' => '1.1'],
                ['entry' => '2.2'],
                ['entry' => '2.3'],
            ]],
            ['entry' => '3.0'],
        ]);

        $diff = $tree->diff();
        $this->assertInstanceOf(CollectionTreeDiff::class, $diff);
        $this->assertEquals(['1.4', '3.0'], $diff->added());
        $this->assertEquals(['1.3'], $diff->removed());
        $this->assertEquals(['1.1', '2.2', '2.3'], $diff->moved());
        $this->assertEquals(['1.1'], $diff->ancestryChanged());
    }

    #[Test]
    public function it_fires_a_saving_event()
    {
        Event::fake();

        $collection = Collection::make('test')->structureContents(['root' => true]);
        Collection::shouldReceive('findByHandle')->with('test')->andReturn($collection);

        $tree = $collection->structure()->makeTree('en');
        $tree->save();

        Event::assertDispatched(CollectionTreeSaving::class);

        $this->assertFileExists($tree->path());
    }

    #[Test]
    public function returning_false_in_collection_tree_saving_stops_saving()
    {
        Event::listen(CollectionTreeSaving::class, function (CollectionTreeSaving $event) {
            return false;
        });

        $collection = Collection::make('test')->structureContents(['root' => true]);
        Collection::shouldReceive('findByHandle')->with('test')->andReturn($collection);

        $tree = $collection->structure()->makeTree('en');
        $tree->save();

        $this->assertFileDoesNotExist($tree->path());
    }
}
