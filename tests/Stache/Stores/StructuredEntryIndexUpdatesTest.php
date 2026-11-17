<?php

namespace Tests\Stache\Stores;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Stache;
use Statamic\Stache\Indexes\Value;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StructuredEntryIndexUpdatesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function writesTo(string $index, callable $callback): int
    {
        $writes = 0;

        Event::listen(KeyWritten::class, function ($event) use ($index, &$writes) {
            if ($event->key === "stache::indexes::entries::pages::$index") {
                $writes++;
            }
        });

        $callback();

        return $writes;
    }

    private function pages()
    {
        $collection = tap(Collection::make('pages')->routes('{parent_uri}/{slug}')->structureContents(['root' => false]))->save();

        collect(['one', 'two', 'three'])->each(fn ($id) => EntryFactory::collection('pages')->id($id)->slug($id)->create());

        return $collection;
    }

    #[Test]
    public function updating_the_order_of_several_entries_writes_the_index_once()
    {
        $collection = $this->pages();
        $store = Stache::store('entries')->store('pages');
        $store->index('order')->load();

        $collection->structure()->in('en')->tree([['entry' => 'three'], ['entry' => 'one'], ['entry' => 'two']])->saveQuietly();

        $writes = $this->writesTo('order', fn () => $store->updateOrders(['one', 'two', 'three']));

        $this->assertSame(1, $writes);
        $this->assertEquals(['one' => 2, 'two' => 3, 'three' => 1], $store->index('order')->items()->all());
    }

    #[Test]
    public function updating_the_parents_and_uris_of_several_entries_writes_each_index_once()
    {
        $collection = $this->pages();
        $store = Stache::store('entries')->store('pages');
        $store->index('parent')->load();
        $store->index('uri')->load();

        $collection->structure()->in('en')->tree([['entry' => 'one', 'children' => [['entry' => 'two'], ['entry' => 'three']]]])->saveQuietly();

        $this->assertSame(1, $this->writesTo('parent', fn () => $store->updateParents(['two', 'three'])));
        $this->assertSame(1, $this->writesTo('uri', fn () => $store->updateUris(['two', 'three'])));

        $this->assertEquals(['one' => null, 'two' => 'one', 'three' => 'one'], $store->index('parent')->items()->all());
        $this->assertEquals(['one' => '/one', 'two' => '/one/two', 'three' => '/one/three'], $store->index('uri')->items()->all());
    }

    #[Test]
    public function ids_that_arent_entries_are_passed_over()
    {
        $this->pages();
        $store = Stache::store('entries')->store('pages');
        $before = $store->index('order')->load()->items()->all();

        $this->assertSame(0, $this->writesTo('order', fn () => $store->updateOrders(['missing'])));
        $this->assertEquals($before, $store->index('order')->items()->all());
    }

    #[Test]
    public function an_index_with_its_own_way_of_updating_an_item_is_still_asked_to()
    {
        $this->pages();
        $store = Stache::store('entries')->store('pages');

        $index = new class($store, 'title') extends Value
        {
            public array $updated = [];

            public function updateItem($item)
            {
                $this->updated[] = $item->id();
            }
        };

        $index->updateItems(collect(['one', 'two'])->map(fn ($id) => \Statamic\Facades\Entry::find($id)));

        $this->assertSame(['one', 'two'], $index->updated);
    }
}
