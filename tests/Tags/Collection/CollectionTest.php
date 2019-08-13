<?php

namespace Tests\Tags\Collection;

use Statamic\API;
use Tests\TestCase;
use Statamic\API\Antlers;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Illuminate\Support\Carbon;
use Statamic\Tags\Collection\Entries;
use Statamic\Tags\Collection\Collection;
use Facades\Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;

class CollectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    function setUp(): void
    {
        parent::setUp();

        $this->music = API\Collection::make('music')->save();
        $this->art = API\Collection::make('art')->save();
        $this->books = API\Collection::make('books')->save();
        $this->foods = API\Collection::make('foods')->save();

        $this->collectionTag = (new Collection)
            ->setParser(Antlers::parser())
            ->setContext([]);
    }

    protected function makeEntry($collection)
    {
        return EntryFactory::collection($collection)->make();
    }

    protected function makePosts()
    {
        $this->makeEntry($this->music)->set('title', 'I Love Guitars')->save();
        $this->makeEntry($this->music)->set('title', 'I Love Drums')->save();
        $this->makeEntry($this->music)->set('title', 'I Hate Flutes')->save();

        $this->makeEntry($this->art)->set('title', 'I Love Drawing')->save();
        $this->makeEntry($this->art)->set('title', 'I Love Painting')->save();
        $this->makeEntry($this->art)->set('title', 'I Hate Sculpting')->save();

        $this->makeEntry($this->books)->set('title', 'I Love Tolkien')->save();
        $this->makeEntry($this->books)->set('title', 'I Love Lewis')->save();
        $this->makeEntry($this->books)->set('title', 'I Hate Martin')->save();
    }

    /** @test */
    function it_throws_an_exception_for_an_invalid_collection()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => 'music|unknown']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Collection [unknown] does not exist.');

        $this->collectionTag->index();
    }

    /** @test */
    function it_gets_entries_from_multiple_collections()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => 'music|art']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['in' => 'music|art']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['folder' => 'music|art']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['use' => 'music|art']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['collection' => 'music|art']);
        $this->assertCount(6, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_all_collections()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => '*']);
        $this->assertCount(9, $this->collectionTag->index());

        $this->setTagParameters(['in' => '*']);
        $this->assertCount(9, $this->collectionTag->index());

        $this->setTagParameters(['folder' => '*']);
        $this->assertCount(9, $this->collectionTag->index());

        $this->setTagParameters(['use' => '*']);
        $this->assertCount(9, $this->collectionTag->index());

        $this->setTagParameters(['collection' => '*']);
        $this->assertCount(9, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_all_collections_excluding_one()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => '*', 'not_from' => 'art']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['in' => '*', 'not_in' => 'art']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['folder' => '*', 'not_folder' => 'art']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['use' => '*', 'dont_use' => 'art']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['collection' => '*', 'dont_use' => 'art']);
        $this->assertCount(6, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_multiple_collections_using_params()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => 'music|art', 'title:contains' => 'love']);
        $this->assertCount(4, $this->collectionTag->index());

        $this->setTagParameters(['in' => 'music|art', 'title:contains' => 'love']);
        $this->assertCount(4, $this->collectionTag->index());

        $this->setTagParameters(['folder' => 'music|art', 'title:contains' => 'love']);
        $this->assertCount(4, $this->collectionTag->index());

        $this->setTagParameters(['use' => 'music|art', 'title:contains' => 'love']);
        $this->assertCount(4, $this->collectionTag->index());

        $this->setTagParameters(['collection' => 'music|art', 'title:contains' => 'love']);
        $this->assertCount(4, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_all_collections_using_params()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => '*', 'title:contains' => 'love']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['in' => '*', 'title:contains' => 'love']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['folder' => '*', 'title:contains' => 'love']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['use' => '*', 'title:contains' => 'love']);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['collection' => '*', 'title:contains' => 'love']);
        $this->assertCount(6, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_all_collections_excluding_some_with_params()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => '*', 'not_from' => 'art|music', 'title:contains' => 'love']);
        $this->assertCount(2, $this->collectionTag->index());

        $this->setTagParameters(['in' => '*', 'not_in' => 'art|music', 'title:contains' => 'love']);
        $this->assertCount(2, $this->collectionTag->index());

        $this->setTagParameters(['folder' => '*', 'not_folder' => 'art|music', 'title:contains' => 'love']);
        $this->assertCount(2, $this->collectionTag->index());

        $this->setTagParameters(['use' => '*', 'dont_use' => 'art|music', 'title:contains' => 'love']);
        $this->assertCount(2, $this->collectionTag->index());

        $this->setTagParameters(['collection' => '*', 'not_collection' => 'art|music', 'title:contains' => 'love']);
        $this->assertCount(2, $this->collectionTag->index());
    }

    /** @test */
    function it_counts_entries_in_a_collection()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => '*']);
        $this->assertEquals(9, $this->collectionTag->count());

        $this->setTagParameters(['in' => '*']);
        $this->assertEquals(9, $this->collectionTag->count());

        $this->setTagParameters(['folder' => '*']);
        $this->assertEquals(9, $this->collectionTag->count());

        $this->setTagParameters(['use' => '*']);
        $this->assertEquals(9, $this->collectionTag->count());

        $this->setTagParameters(['collection' => '*']);
        $this->assertEquals(9, $this->collectionTag->count());
    }

    /** @test */
    function it_counts_entries_in_a_collection_with_params()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => '*', 'not_from' => 'art|music', 'title:contains' => 'love']);
        $this->assertEquals(2, $this->collectionTag->count());

        $this->setTagParameters(['in' => '*', 'not_in' => 'art|music', 'title:contains' => 'love']);
        $this->assertEquals(2, $this->collectionTag->count());

        $this->setTagParameters(['folder' => '*', 'not_folder' => 'art|music', 'title:contains' => 'love']);
        $this->assertEquals(2, $this->collectionTag->count());

        $this->setTagParameters(['use' => '*', 'dont_use' => 'art|music', 'title:contains' => 'love']);
        $this->assertEquals(2, $this->collectionTag->count());

        $this->setTagParameters(['collection' => '*', 'not_collection' => 'art|music', 'title:contains' => 'love']);
        $this->assertEquals(2, $this->collectionTag->count());
    }

    /** @test */
    function it_can_get_next_in_asc_collection()
    {
        $this->foods->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-04-10 13:00'));

        $this->makeEntry($this->foods)->date('2019-02-01')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods)->date('2019-02-06')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods)->date('2019-02-06')->set('title', 'Carrot')->save();
        $this->makeEntry($this->foods)->date('2019-03-02')->set('title', 'Danish')->save();
        $this->makeEntry($this->foods)->date('2019-03-03')->set('title', 'Egg')->save();
        $this->makeEntry($this->foods)->date('2019-03-04')->set('title', 'Fig')->save();
        $this->makeEntry($this->foods)->date('2019-03-10')->set('title', 'Grape')->save();
        $this->makeEntry($this->foods)->date('2019-03-10')->set('title', 'Hummus')->save();
        $this->makeEntry($this->foods)->date('2019-03-11')->set('title', 'Ice Cream')->save();

        $currentId = API\Entry::all()->first(function ($entry) {
            return $entry->get('title') === 'Egg';
        })->id();

        $this->setTagParameters([
            'in' => 'foods',
            'current' => $currentId,
            'order_by' => 'date|title:desc',
            'limit' => 2
        ]);

        $this->assertEquals(
            ['Fig', 'Hummus'],
            $this->collectionTag->next()->map->get('title')->all()
        );
    }

    /** @test */
    function it_can_get_next_in_desc_collection()
    {
        $this->foods->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-04-10 13:00'));

        $this->makeEntry($this->foods)->date('2019-02-01')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods)->date('2019-02-06')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods)->date('2019-02-06')->set('title', 'Carrot')->save();
        $this->makeEntry($this->foods)->date('2019-03-02')->set('title', 'Danish')->save();
        $this->makeEntry($this->foods)->date('2019-03-03')->set('title', 'Egg')->save();
        $this->makeEntry($this->foods)->date('2019-03-04')->set('title', 'Fig')->save();
        $this->makeEntry($this->foods)->date('2019-03-10')->set('title', 'Grape')->save();
        $this->makeEntry($this->foods)->date('2019-03-10')->set('title', 'Hummus')->save();
        $this->makeEntry($this->foods)->date('2019-03-11')->set('title', 'Ice Cream')->save();

        $currentId = API\Entry::all()->first(function ($entry) {
            return $entry->get('title') === 'Egg';
        })->id();

        $this->setTagParameters([
            'in' => 'foods',
            'current' => $currentId,
            // 'order_by' => 'date:desc|title', // Should default to this if not explicitly set.
            'limit' => 2
        ]);

        $this->assertEquals(
            ['Hummus', 'Fig'],
            $this->collectionTag->next()->map->get('title')->all()
        );

        $this->setTagParameters([
            'in' => 'foods',
            'current' => $currentId,
            'order_by' => 'date:asc|title:desc', // Intentionally reverse order.
            'limit' => 2
        ]);

        $this->assertEquals(
            ['Fig', 'Hummus'],
            $this->collectionTag->next()->map->get('title')->all()
        );
    }

    /** @test */
    function it_can_get_previous_in_asc_collection()
    {
        $this->foods->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-04-10 13:00'));

        $this->makeEntry($this->foods)->date('2019-02-01')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods)->date('2019-02-06')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods)->date('2019-02-06')->set('title', 'Carrot')->save();
        $this->makeEntry($this->foods)->date('2019-03-02')->set('title', 'Danish')->save();
        $this->makeEntry($this->foods)->date('2019-03-03')->set('title', 'Egg')->save();
        $this->makeEntry($this->foods)->date('2019-03-04')->set('title', 'Fig')->save();
        $this->makeEntry($this->foods)->date('2019-03-10')->set('title', 'Grape')->save();
        $this->makeEntry($this->foods)->date('2019-03-10')->set('title', 'Hummus')->save();
        $this->makeEntry($this->foods)->date('2019-03-11')->set('title', 'Ice Cream')->save();

        $currentId = API\Entry::all()->first(function ($entry) {
            return $entry->get('title') === 'Egg';
        })->id();

        $this->setTagParameters([
            'in' => 'foods',
            'current' => $currentId,
            'order_by' => 'date|title:desc',
            'limit' => 2
        ]);

        $this->assertEquals(
            ['Banana', 'Danish'],
            $this->collectionTag->previous()->map->get('title')->all()
        );
    }

    /** @test */
    function it_can_get_previous_in_desc_collection()
    {
        $this->foods->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-04-10 13:00'));

        $this->makeEntry($this->foods)->date('2019-02-01')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods)->date('2019-02-06')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods)->date('2019-02-06')->set('title', 'Carrot')->save();
        $this->makeEntry($this->foods)->date('2019-03-02')->set('title', 'Danish')->save();
        $this->makeEntry($this->foods)->date('2019-03-03')->set('title', 'Egg')->save();
        $this->makeEntry($this->foods)->date('2019-03-04')->set('title', 'Fig')->save();
        $this->makeEntry($this->foods)->date('2019-03-10')->set('title', 'Grape')->save();
        $this->makeEntry($this->foods)->date('2019-03-10')->set('title', 'Hummus')->save();
        $this->makeEntry($this->foods)->date('2019-03-11')->set('title', 'Ice Cream')->save();

        $currentId = API\Entry::all()->first(function ($entry) {
            return $entry->get('title') === 'Egg';
        })->id();

        $this->setTagParameters([
            'in' => 'foods',
            'current' => $currentId,
            // 'order_by' => 'date:desc|title', // Should default to this if not explicitly set.
            'limit' => 2
        ]);

        $this->assertEquals(
            ['Danish', 'Banana'],
            $this->collectionTag->previous()->map->get('title')->all()
        );

        $this->setTagParameters([
            'in' => 'foods',
            'current' => $currentId,
            'order_by' => 'date:asc|title:desc', // Intentionally reverse order.
            'limit' => 2
        ]);

        $this->assertEquals(
            ['Banana', 'Danish'],
            $this->collectionTag->previous()->map->get('title')->all()
        );
    }

    private function setTagParameters($parameters)
    {
        $this->collectionTag->setParameters($parameters);
    }
}
