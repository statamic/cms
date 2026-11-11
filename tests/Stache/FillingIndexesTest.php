<?php

namespace Tests\Stache;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FillingIndexesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function store($collection)
    {
        return Stache::store('entries')->store($collection);
    }

    private function createContent()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en_US'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr_FR'],
        ]);

        Taxonomy::make('tags')->sites(['en', 'fr'])->save();

        Collection::make('blog')->routes('blog/{slug}')->dated(true)->taxonomies(['tags'])->sites(['en', 'fr'])->save();
        Collection::make('pages')->routes('{parent_uri}/{slug}')->structureContents(['root' => true])->sites(['en', 'fr'])->save();

        EntryFactory::id('one')->locale('en')->collection('blog')->slug('one')->date('2024-01-01')->data(['title' => 'One', 'tags' => ['alfa', 'bravo']])->create();
        EntryFactory::id('two')->locale('en')->collection('blog')->slug('two')->date('2024-02-01')->data(['title' => 'Two', 'tags' => ['bravo']])->create();
        EntryFactory::id('un')->locale('fr')->collection('blog')->slug('un')->date('2024-01-01')->origin('one')->data([])->create();

        EntryFactory::id('home')->locale('en')->collection('pages')->slug('home')->data(['title' => 'Home'])->create();
        EntryFactory::id('about')->locale('en')->collection('pages')->slug('about')->data(['title' => 'About'])->create();
        EntryFactory::id('a-propos')->locale('fr')->collection('pages')->slug('a-propos')->origin('about')->data(['title' => 'À propos'])->create();

        Stache::clear();
    }

    #[Test]
    public function asking_for_one_index_fills_the_others_in_the_same_pass()
    {
        $this->createContent();

        $store = $this->store('blog');

        $this->assertFalse($store->resolveIndexes()->get('title')->isCached());

        $store->index('slug');

        $this->assertTrue($store->resolveIndexes()->get('title')->isCached());
        $this->assertTrue($store->resolveIndexes()->get('uri')->isCached());
        $this->assertTrue($store->resolveIndexes()->get('tags')->isCached());
    }

    #[Test]
    public function indexes_hold_what_they_would_if_each_had_read_every_entry_for_itself()
    {
        $this->createContent();

        Stache::warm();

        $store = $this->store('blog');

        foreach ($store->resolveIndexes() as $name => $index) {
            $expected = $index->getItems();

            $this->assertSame(array_keys($expected), $index->items()->keys()->all(), $name);
            $this->assertEquals($expected, $index->items()->all(), $name);
        }

        $this->assertEquals(['one' => '/blog/one', 'two' => '/blog/two', 'un' => '/blog/un'], $store->index('uri')->items()->all());
    }

    #[Test]
    public function a_localization_is_indexed_with_the_values_it_takes_from_its_origin()
    {
        $this->createContent();

        $this->assertEquals('One', $this->store('blog')->index('title')->get('un'));
        $this->assertEquals(['alfa', 'bravo'], $this->store('blog')->index('tags')->get('un'));
        $this->assertEquals('one', $this->store('blog')->index('origin')->get('un'));

        $this->assertEquals(['un'], Entry::query()->where('collection', 'blog')->where('site', 'fr')->where('title', 'One')->get()->map->id()->all());
    }

    #[Test]
    public function entries_of_a_collection_mounted_on_another_entry_are_indexed_once_every_entry_exists()
    {
        $this->createContent();

        // Building a uri looks the mount up, which goes through every collection's entries.
        Collection::findByHandle('blog')->routes('{mount}/{slug}')->mount('home')->save();
        Stache::clear();

        $store = $this->store('blog');

        $this->assertEquals('/home/one', $store->index('uri')->get('one'));
        $this->assertEquals('/home/two', $store->index('uri')->get('two'));
        $this->assertEquals(['one' => 'One', 'two' => 'Two', 'un' => 'One'], $store->index('title')->items()->all());
        $this->assertNull(Blink::store('structure-entries')->get('one'));
    }

    #[Test]
    public function a_computed_value_that_queries_the_collection_is_indexed_once_every_entry_exists()
    {
        // Registered first, as an app would in a service provider.
        Collection::computed('blog', 'in_this_site', function ($entry) {
            return Entry::query()->where('collection', 'blog')->where('site', $entry->locale())->count();
        });

        $this->createContent();

        $store = $this->store('blog');
        $store->cacheIndexUsage('in_this_site');

        $this->assertEquals(['one' => 2, 'two' => 2, 'un' => 1], $store->index('in_this_site')->items()->all());
        $this->assertEquals(['one' => 'en', 'two' => 'en', 'un' => 'fr'], $store->index('site')->items()->all());
    }

    #[Test]
    public function warming_leaves_indexes_alone_when_updating_them_is_disabled()
    {
        $this->createContent();

        Stache::disableUpdatingIndexes();
        Stache::warm();

        $this->assertFalse($this->store('blog')->resolveIndexes()->get('title')->isCached());
    }

    #[Test]
    public function term_associations_are_built_from_the_entries_indexes()
    {
        $this->createContent();

        $associations = Stache::store('terms')->store('tags')->index('associations');

        $this->assertEquals(['one', 'un'], collect($associations->forSlug('alfa'))->pluck('entry')->sort()->values()->all());
        $this->assertEquals(['one', 'two', 'un'], collect($associations->forSlug('bravo'))->pluck('entry')->sort()->values()->all());
        $this->assertEquals(['en', 'fr'], collect($associations->forSlug('alfa'))->pluck('site')->sort()->values()->all());
    }

    #[Test]
    public function entries_arent_kept_in_memory_once_they_have_been_indexed()
    {
        $this->createContent();
        Blink::store('structure-entries')->flush();

        $this->store('blog')->index('slug');

        $this->assertNull(Blink::store('structure-entries')->get('one'));
        $this->assertNull(Blink::store('structure-entries')->get('un'));

        // A structure's uri, parent and order indexes are built from its entries afterwards.
        $this->store('pages')->index('slug');

        $this->assertNotNull(Blink::store('structure-entries')->get('about'));
    }

    #[Test]
    public function rebuilding_one_index_leaves_the_others_alone()
    {
        $this->createContent();

        $store = $this->store('blog');
        $store->index('slug');

        $title = $store->resolveIndexes()->get('title');
        Stache::cacheStore()->forever($title->cacheKey(), ['untouched' => true]);

        $store->resolveIndexes()->get('slug')->update();

        $this->assertEquals(['untouched' => true], Stache::cacheStore()->get($title->cacheKey()));
        $this->assertEquals(['one' => 'one', 'two' => 'two', 'un' => 'un'], $store->resolveIndexes()->get('slug')->items()->all());
    }

    #[Test]
    public function warming_again_in_the_same_process_fills_the_indexes_again()
    {
        $this->createContent();

        Stache::warm();
        $this->assertEquals('One', $this->store('blog')->index('title')->get('one'));

        // Something that leaves the indexes behind what is cached.
        $this->cacheWithTitle('one', 'Changed');

        Stache::warm();
        $this->assertEquals('Changed', $this->store('blog')->index('title')->get('one'));
        $this->assertEquals('Changed', $this->store('blog')->index('title')->get('un'));
    }

    #[Test]
    public function a_store_another_store_needed_during_a_warm_isnt_filled_twice()
    {
        $this->createContent();

        $made = 0;
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Cache\Events\KeyWritten::class, function ($event) use (&$made) {
            if ($event->key === 'stache::indexes::entries::blog::title') {
                $made++;
            }
        });

        // The terms are warmed before the entries, and their associations need the entries' indexes.
        Stache::warm();

        $this->assertEquals(1, $made);
    }

    private function cacheWithTitle($id, $title)
    {
        $store = $this->store('blog');
        $key = (fn () => $this->getItemCacheKey($id))->call($store);

        Stache::cacheStore()->forever($key, Stache::cacheStore()->get($key)->set('title', $title));
        Blink::flush();
    }
}
