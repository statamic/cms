<?php

namespace Tests\Stache\Stores;

use Mockery;
use Statamic\API;
use Tests\TestCase;
use Statamic\API\Stache;
use Statamic\API\Collection;
use Illuminate\Support\Carbon;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\EntriesStore;
use Tests\PreventSavingStacheItemsToDisk;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Stache\Stores\CollectionEntriesStore;

class EntriesStoreTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    function setUp(): void
    {
        parent::setUp();

        $this->parent = (new EntriesStore)->directory(
            $this->directory = __DIR__.'/../__fixtures__/content/collections'
        );

        Stache::registerStore($this->parent);

        Stache::store('collections')->directory($this->directory);
    }

    /** @test */
    function it_gets_nested_files()
    {
        tap($this->parent->store('alphabetical'), function ($store) {
            $files = Traverser::filter([$store, 'getItemFilter'])->traverse($store);

            $this->assertEquals(collect([
                $this->directory.'/alphabetical/alpha.md',
                $this->directory.'/alphabetical/bravo.md',
                $this->directory.'/alphabetical/zulu.md',
            ])->sort()->values()->all(), $files->keys()->sort()->values()->all());
        });

        tap($this->parent->store('blog'), function ($store) {
            $files = Traverser::filter([$store, 'getItemFilter'])->traverse($store);

            $this->assertEquals(collect([
                $this->directory.'/blog/2017-25-12.christmas.md',
                $this->directory.'/blog/2018-07-04.fourth-of-july.md',
            ])->sort()->values()->all(), $files->keys()->sort()->values()->all());
        });

        tap($this->parent->store('numeric'), function ($store) {
            $files = Traverser::filter([$store, 'getItemFilter'])->traverse($store);

            $this->assertEquals(collect([
                $this->directory.'/numeric/1.one.md',
                $this->directory.'/numeric/2.two.md',
                $this->directory.'/numeric/3.three.md',
            ])->sort()->values()->all(), $files->keys()->sort()->values()->all());
        });

        tap($this->parent->store('pages'), function ($store) {
            $files = Traverser::filter([$store, 'getItemFilter'])->traverse($store);

            $this->assertEquals(collect([
                $this->directory.'/pages/about.md',
                $this->directory.'/pages/about/board.md',
                $this->directory.'/pages/about/directors.md',
                $this->directory.'/pages/blog.md',
                $this->directory.'/pages/contact.md',
                $this->directory.'/pages/home.md',
            ])->sort()->values()->all(), $files->keys()->sort()->values()->all());
        });
    }

    /** @test */
    function it_makes_entry_instances_from_files()
    {
        API\Collection::shouldReceive('findByHandle')->with('blog')->andReturn(
            (new \Statamic\Data\Entries\Collection)->dated(true)
        );

        $item = $this->parent->store('blog')->makeItemFromFile(
            $this->directory.'/blog/2017-01-02.my-post.md',
            "id: 123\ntitle: Example\nfoo: bar"
        );

        $this->assertInstanceOf(Entry::class, $item);
        $this->assertEquals('123', $item->id());
        $this->assertEquals('Example', $item->get('title'));
        $this->assertEquals(['title' => 'Example', 'foo' => 'bar'], $item->data());
        $this->assertTrue(Carbon::createFromFormat('Y-m-d H:i', '2017-01-02 00:00')->eq($item->date()));
        $this->assertEquals('my-post', $item->slug());
        $this->assertTrue($item->published());
    }

    /** @test */
    function it_uses_the_id_of_the_entry_as_the_item_key()
    {
        $entry = Mockery::mock();
        $entry->shouldReceive('id')->andReturn('test');
        $entry->shouldReceive('collectionHandle')->andReturn('example');

        $this->assertEquals(
            'test',
            $this->parent->store('test')->getItemKey($entry)
        );
    }

    /** @test */
    function it_saves_to_disk()
    {
        $entry = API\Entry::make()
            ->id('123')
            ->slug('test')
            ->date('2017-07-04')
            ->collection('blog');

        $this->parent->store('blog')->save($entry);

        $this->assertFileEqualsString($path = $this->directory.'/blog/2017-07-04.test.md', $entry->fileContents());
        @unlink($path);
        $this->assertFileNotExists($path);
    }
}
