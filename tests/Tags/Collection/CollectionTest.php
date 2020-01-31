<?php

namespace Tests\Tags\Collection;

use Statamic\Facades;
use Tests\TestCase;
use Statamic\Facades\Antlers;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Illuminate\Support\Carbon;
use Statamic\Tags\Collection\Entries;
use Statamic\Tags\Collection\Collection;
use Facades\Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Facades\Blueprint;

class CollectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    function setUp(): void
    {
        parent::setUp();

        $this->music = Facades\Collection::make('music')->save();
        $this->art = Facades\Collection::make('art')->save();
        $this->books = Facades\Collection::make('books')->save();
        $this->foods = Facades\Collection::make('foods')->save();

        $this->collectionTag = (new Collection)
            ->setParser(Antlers::parser())
            ->setContext([]);
    }

    protected function makeEntry($collection, $slug)
    {
        return EntryFactory::collection($collection)->slug($slug)->make();
    }

    protected function makePosts()
    {
        $this->makeEntry($this->music, 'a')->set('title', 'I Love Guitars')->save();
        $this->makeEntry($this->music, 'b')->set('title', 'I Love Drums')->save();
        $this->makeEntry($this->music, 'c')->set('title', 'I Hate Flutes')->save();

        $this->makeEntry($this->art, 'd')->set('title', 'I Love Drawing')->save();
        $this->makeEntry($this->art, 'e')->set('title', 'I Love Painting')->save();
        $this->makeEntry($this->art, 'f')->set('title', 'I Hate Sculpting')->save();

        $this->makeEntry($this->books, 'g')->set('title', 'I Love Tolkien')->save();
        $this->makeEntry($this->books, 'h')->set('title', 'I Love Lewis')->save();
        $this->makeEntry($this->books, 'i')->set('title', 'I Hate Martin')->save();
    }

    /** @test */
    function it_throws_an_exception_for_an_invalid_collection()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => 'music|unknown']);

        $this->expectException(CollectionNotFoundException::class);
        $this->expectExceptionMessage('Collection [unknown] not found');

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
    function it_can_get_previous_and_next_entries_in_a_dated_desc_collection()
    {
        $this->foods->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-04-10 13:00'));

        $this->makeEntry($this->foods, 'a')->date('2019-02-01')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods, 'b')->date('2019-02-06')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods, 'c')->date('2019-02-06')->set('title', 'Carrot')->save();
        $this->makeEntry($this->foods, 'd')->date('2019-03-02')->set('title', 'Danish')->save();
        $this->makeEntry($this->foods, 'e')->date('2019-03-03')->set('title', 'Egg')->save();
        $this->makeEntry($this->foods, 'f')->date('2019-03-04')->set('title', 'Fig')->save();
        $this->makeEntry($this->foods, 'g')->date('2019-03-10')->set('title', 'Grape')->save();
        $this->makeEntry($this->foods, 'h')->date('2019-03-10')->set('title', 'Hummus')->save();
        $this->makeEntry($this->foods, 'i')->date('2019-03-11')->set('title', 'Ice Cream')->save();

        $currentId = $this->findEntryByTitle('Egg')->id();

        $orderBy = 'date:desc|title:asc';
            // Grape
            // Hummus
            // Fig
            // Egg (current)
            // Danish
            // Banana
            // Carrot

        $this->setTagParameters(['in' => 'foods', 'current' => $currentId, 'order_by' => $orderBy, 'limit' => 1]);

        $this->assertEquals(['Danish'], $this->runTagAndGetTitles('next'));
        $this->assertEquals(['Danish'], $this->runTagAndGetTitles('older')); // Alias of next when date:desc
        $this->assertEquals(['Fig'], $this->runTagAndGetTitles('previous'));
        $this->assertEquals(['Fig'], $this->runTagAndGetTitles('newer')); // Alias of previous when date:desc

        $this->setTagParameters(['in' => 'foods', 'current' => $currentId, 'order_by' => $orderBy, 'limit' => 3]);

        $this->assertEquals(['Danish', 'Banana', 'Carrot'], $this->runTagAndGetTitles('next'));
        $this->assertEquals(['Danish', 'Banana', 'Carrot'], $this->runTagAndGetTitles('older')); // Alias of next when date:desc
        $this->assertEquals(['Grape', 'Hummus', 'Fig'], $this->runTagAndGetTitles('previous'));
        $this->assertEquals(['Grape', 'Hummus', 'Fig'], $this->runTagAndGetTitles('newer')); // Alias of prev when date:desc
    }

    /** @test */
    function it_can_get_previous_and_next_entries_in_a_dated_asc_collection()
    {
        $this->foods->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-04-10 13:00'));

        $this->makeEntry($this->foods, 'a')->date('2019-02-01')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods, 'b')->date('2019-02-06')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods, 'c')->date('2019-02-06')->set('title', 'Carrot')->save();
        $this->makeEntry($this->foods, 'd')->date('2019-03-02')->set('title', 'Danish')->save();
        $this->makeEntry($this->foods, 'e')->date('2019-03-03')->set('title', 'Egg')->save();
        $this->makeEntry($this->foods, 'f')->date('2019-03-04')->set('title', 'Fig')->save();
        $this->makeEntry($this->foods, 'g')->date('2019-03-10')->set('title', 'Grape')->save();
        $this->makeEntry($this->foods, 'h')->date('2019-03-10')->set('title', 'Hummus')->save();
        $this->makeEntry($this->foods, 'i')->date('2019-03-11')->set('title', 'Ice Cream')->save();

        $currentId = $this->findEntryByTitle('Egg')->id();

        $orderBy = 'date:asc|title:desc';
            // Carrot
            // Banana
            // Danish
            // Egg (current)
            // Fig
            // Hummus
            // Grape

        $this->setTagParameters(['in' => 'foods', 'current' => $currentId, 'order_by' => $orderBy, 'limit' => 1]);

        $this->assertEquals(['Fig'], $this->runTagAndGetTitles('next'));
        $this->assertEquals(['Fig'], $this->runTagAndGetTitles('newer')); // Alias of next when date:desc
        $this->assertEquals(['Danish'], $this->runTagAndGetTitles('previous'));
        $this->assertEquals(['Danish'], $this->runTagAndGetTitles('older')); // Alias of previous when date:desc

        $this->setTagParameters(['in' => 'foods', 'current' => $currentId, 'order_by' => $orderBy, 'limit' => 3]);

        $this->assertEquals(['Fig', 'Hummus', 'Grape'], $this->runTagAndGetTitles('next'));
        $this->assertEquals(['Fig', 'Hummus', 'Grape'], $this->runTagAndGetTitles('newer')); // Alias of next when date:desc
        $this->assertEquals(['Carrot', 'Banana', 'Danish'], $this->runTagAndGetTitles('previous'));
        $this->assertEquals(['Carrot', 'Banana', 'Danish'], $this->runTagAndGetTitles('older')); // Alias of prev when date:desc
    }

    /** @test */
    function it_adds_defaults_for_missing_items_based_on_blueprint()
    {
        $blueprint = Blueprint::make('test')->setContents(['fields' => [['handle' => 'title', 'field' => ['type' => 'text']]]]);
        Blueprint::shouldReceive('find')->with('test')->andReturn($blueprint);
        $this->foods->entryBlueprints(['test']);

        $this->makeEntry($this->foods, 'a')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods, 'b')->save();
        $this->makeEntry($this->foods, 'c')->set('title', null)->save();
        $this->makeEntry($this->foods, 'd')->set('title', 'Banana')->save();

        $this->setTagParameters(['in' => 'foods']);

        $items = collect($this->collectionTag->index()->toAugmentedArray())->mapWithKeys(function ($item) {
            return [$item['slug']->value() => $item['title']->value()];
        })->all();

        $this->assertEquals([
            'a' => 'Apple',
            'b' => null,
            'c' => null,
            'd' => 'Banana'
        ], $items);
    }

    private function setTagParameters($parameters)
    {
        $this->collectionTag->setParameters($parameters);
    }

    public function findEntryByTitle($title)
    {
        return Facades\Entry::all()->first(function ($entry) use ($title) {
            return $entry->get('title') === $title;
        });
    }

    protected function runTagAndGetTitles($tagMethod)
    {
        return $this->collectionTag->{$tagMethod}()->map->get('title')->values()->all();
    }
}
