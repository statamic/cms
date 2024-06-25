<?php

namespace Tests\Tags\Collection;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Facades;
use Statamic\Facades\Antlers;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Entry;
use Statamic\Structures\CollectionStructure;
use Statamic\Tags\Collection\Collection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $music;
    private $art;
    private $books;
    private $foods;
    private $collectionTag;

    public function setUp(): void
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
        return EntryFactory::id($slug)->collection($collection)->slug($slug)->make();
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

    #[Test]
    public function it_throws_an_exception_for_an_invalid_collection()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => 'music|unknown']);

        $this->expectException(CollectionNotFoundException::class);
        $this->expectExceptionMessage('Collection [unknown] not found');

        $this->collectionTag->index();
    }

    #[Test]
    public function it_gets_entries_from_multiple_collections()
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

    #[Test]
    public function it_gets_entries_from_collections_using_collection_objects()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => Facades\Collection::findByHandle('music')]);
        $this->assertCount(3, $this->collectionTag->index());

        $this->setTagParameters(['from' => [
            Facades\Collection::findByHandle('music'),
            Facades\Collection::findByHandle('art'),
        ]]);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['from' => collect([
            Facades\Collection::findByHandle('music'),
            Facades\Collection::findByHandle('art'),
        ])]);
        $this->assertCount(6, $this->collectionTag->index());
    }

    #[Test]
    public function it_gets_entries_from_all_collections()
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

    #[Test]
    public function it_gets_entries_from_all_collections_excluding_one()
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

    #[Test]
    public function it_gets_entries_from_multiple_collections_using_params()
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

    #[Test]
    public function it_gets_entries_from_all_collections_using_params()
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

    #[Test]
    public function it_gets_entries_from_all_collections_excluding_some_with_params()
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

    #[Test]
    public function it_can_exclude_collections_using_collection_objects()
    {
        $this->makePosts();

        $this->setTagParameters(['from' => '*', 'not_from' => Facades\Collection::findByHandle('art')]);
        $this->assertCount(6, $this->collectionTag->index());

        $this->setTagParameters(['from' => '*', 'not_from' => [
            Facades\Collection::findByHandle('music'),
            Facades\Collection::findByHandle('art'),
        ]]);
        $this->assertCount(3, $this->collectionTag->index());
    }

    #[Test]
    public function it_filters_out_redirects()
    {
        $this->makePosts();
        Entry::find('c')->set('redirect', 'http://example.com')->save();
        Entry::find('d')->set('redirect', 'http://example.com')->save();

        // Redirects get filtered out by default.
        $this->setTagParameters(['collection' => '*']);
        $this->assertCount(7, $this->collectionTag->index());

        // Marking as true will include them.
        $this->setTagParameters(['collection' => '*', 'redirects' => true]);
        $this->assertCount(9, $this->collectionTag->index());

        // Aliased to links
        $this->setTagParameters(['collection' => '*', 'links' => true]);
        $this->assertCount(9, $this->collectionTag->index());

        // Shorthand param doesn't exist to get *only* redirects. Users can do it manually with a condition.
        $this->setTagParameters(['collection' => '*', 'redirect:exists' => true]);
        $this->assertCount(2, $this->collectionTag->index());
    }

    #[Test]
    public function it_counts_entries_in_a_collection()
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

    #[Test]
    public function it_counts_entries_in_a_collection_with_params()
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

    #[Test]
    public function it_can_get_previous_and_next_entries_in_a_dated_desc_collection()
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

    #[Test]
    /**
     * https://github.com/statamic/cms/issues/1831
     */
    public function it_can_get_previous_and_next_entries_in_a_dated_desc_collection_when_multiple_entries_share_the_same_date()
    {
        $this->foods->dated(true)->save();

        $this->makeEntry($this->foods, 'a')->date('2023-01-01')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods, 'b')->date('2023-02-05')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods, 'c')->date('2023-02-05')->set('title', 'Carrot')->save();
        $this->makeEntry($this->foods, 'd')->date('2023-03-07')->set('title', 'Danish')->save();

        $this->setTagParameters([
            'in' => 'foods',
            'current' => $this->findEntryByTitle('Carrot')->id(),
            'order_by' => 'date:desc|title:desc',
            'limit' => 1,
        ]);

        $this->assertEquals(['Danish'], $this->runTagAndGetTitles('previous'));
        $this->assertEquals(['Danish'], $this->runTagAndGetTitles('newer')); // Alias of prev when date:desc
        $this->assertEquals(['Banana'], $this->runTagAndGetTitles('next'));
        $this->assertEquals(['Banana'], $this->runTagAndGetTitles('older')); // Alias of next when date:desc
    }

    #[Test]
    public function it_can_get_previous_and_next_entries_in_a_dated_asc_collection()
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

    /**
     * https://github.com/statamic/cms/issues/1831
     */
    #[Test]
    public function it_can_get_previous_and_next_entries_in_a_dated_asc_collection_when_multiple_entries_share_the_same_date()
    {
        $this->foods->dated(true)->save();

        $this->makeEntry($this->foods, 'a')->date('2023-01-01')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods, 'b')->date('2023-02-05')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods, 'c')->date('2023-02-05')->set('title', 'Carrot')->save();
        $this->makeEntry($this->foods, 'd')->date('2023-03-07')->set('title', 'Danish')->save();

        $this->setTagParameters([
            'in' => 'foods',
            'current' => $this->findEntryByTitle('Carrot')->id(),
            'order_by' => 'date:asc|title:asc',
            'limit' => 1,
        ]);

        $this->assertEquals(['Banana'], $this->runTagAndGetTitles('previous'));
        $this->assertEquals(['Banana'], $this->runTagAndGetTitles('older')); // Alias of previous when date:desc
        $this->assertEquals(['Danish'], $this->runTagAndGetTitles('next'));
        $this->assertEquals(['Danish'], $this->runTagAndGetTitles('newer')); // Alias of next when date:asc
    }

    #[Test]
    public function it_can_get_previous_and_next_entries_in_an_orderable_asc_collection()
    {
        $this->makeEntry($this->foods, 'a')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods, 'b')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods, 'c')->set('title', 'Carrot')->save();
        $this->makeEntry($this->foods, 'd')->set('title', 'Danish')->save();
        $this->makeEntry($this->foods, 'e')->set('title', 'Egg')->save();
        $this->makeEntry($this->foods, 'f')->set('title', 'Fig')->save();
        $this->makeEntry($this->foods, 'g')->set('title', 'Grape')->save();
        $this->makeEntry($this->foods, 'h')->set('title', 'Hummus')->save();
        $this->makeEntry($this->foods, 'i')->set('title', 'Ice Cream')->save();

        $structure = $this->makeStructure([
            ['entry' => 'c'], // Carrot
            ['entry' => 'h'], // Hummus
            ['entry' => 'a'], // Apple
            ['entry' => 'i'], // Ice Cream
            ['entry' => 'b'], // Banana
            ['entry' => 'f'], // Fig
            ['entry' => 'g'], // Grape
            ['entry' => 'e'], // Egg
            ['entry' => 'd'], // Danish
        ], 'foods')->maxDepth(1);

        $this->foods->structure($structure)->save();

        $currentId = $this->findEntryByTitle('Banana')->id();

        $orderBy = 'order:asc';
        // Hummus
        // Apple
        // Ice Cream
        // Banana (current)
        // Fig
        // Grape
        // Egg

        $this->setTagParameters(['in' => 'foods', 'current' => $currentId, 'order_by' => $orderBy, 'limit' => 1]);

        $this->assertEquals(['Fig'], $this->runTagAndGetTitles('next'));
        $this->assertEquals(['Ice Cream'], $this->runTagAndGetTitles('previous'));

        $this->setTagParameters(['in' => 'foods', 'current' => $currentId, 'order_by' => $orderBy, 'limit' => 3]);

        $this->assertEquals(['Fig', 'Grape', 'Egg'], $this->runTagAndGetTitles('next'));
        $this->assertEquals(['Hummus', 'Apple', 'Ice Cream'], $this->runTagAndGetTitles('previous'));
    }

    #[Test]
    public function it_can_get_previous_and_next_entries_in_an_orderable_desc_collection()
    {
        $this->makeEntry($this->foods, 'a')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods, 'b')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods, 'c')->set('title', 'Carrot')->save();
        $this->makeEntry($this->foods, 'd')->set('title', 'Danish')->save();
        $this->makeEntry($this->foods, 'e')->set('title', 'Egg')->save();
        $this->makeEntry($this->foods, 'f')->set('title', 'Fig')->save();
        $this->makeEntry($this->foods, 'g')->set('title', 'Grape')->save();
        $this->makeEntry($this->foods, 'h')->set('title', 'Hummus')->save();
        $this->makeEntry($this->foods, 'i')->set('title', 'Ice Cream')->save();

        $structure = $this->makeStructure([
            ['entry' => 'c'], // Carrot
            ['entry' => 'h'], // Hummus
            ['entry' => 'a'], // Apple
            ['entry' => 'i'], // Ice Cream
            ['entry' => 'b'], // Banana
            ['entry' => 'f'], // Fig
            ['entry' => 'g'], // Grape
            ['entry' => 'e'], // Egg
            ['entry' => 'd'], // Danish
        ], 'foods')->maxDepth(1);

        $this->foods->structure($structure)->save();

        $currentId = $this->findEntryByTitle('Banana')->id();

        $orderBy = 'order:desc';
        // Egg
        // Grape
        // Fig
        // Banana (current)
        // Ice Cream
        // Apple
        // Hummus

        $this->setTagParameters(['in' => 'foods', 'current' => $currentId, 'order_by' => $orderBy, 'limit' => 1]);

        $this->assertEquals(['Ice Cream'], $this->runTagAndGetTitles('next'));
        $this->assertEquals(['Fig'], $this->runTagAndGetTitles('previous'));

        $this->setTagParameters(['in' => 'foods', 'current' => $currentId, 'order_by' => $orderBy, 'limit' => 3]);

        $this->assertEquals(['Ice Cream', 'Apple', 'Hummus'], $this->runTagAndGetTitles('next'));
        $this->assertEquals(['Egg', 'Grape', 'Fig'], $this->runTagAndGetTitles('previous'));
    }

    #[Test]
    public function it_adds_defaults_for_missing_items_based_on_blueprint()
    {
        $blueprint = Blueprint::make('test')->setContents(['fields' => [['handle' => 'title', 'field' => ['type' => 'text']]]]);
        Blueprint::shouldReceive('in')->with('collections/foods')->andReturn(collect([$blueprint]));

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
            'd' => 'Banana',
        ], $items);
    }

    #[Test]
    public function when_using_the_tag_without_any_parameters_that_define_the_collection_it_will_get_the_collection_object_from_context()
    {
        $item = Facades\Collection::make();

        $this->collectionTag->setContext(['collection' => $item]);

        // Without a param that would instruct Statamic which collection to get, we just return the collection from context.
        // Which essentially gives the illusion that the tag wasn't run, and a collection variable was accessed.
        $this->assertEquals($item, $this->collectionTag->setParameters([])->index());

        // Sanity check that *any* parameter isn't the thing that causes it.
        $this->assertEquals($item, $this->collectionTag->setParameters(['something' => 'else'])->index());

        // Using one of the inclusive params results in an Illuminate\Support\Collection (ie. a list of entries)
        $this->assertInstanceOf(SupportCollection::class, $this->collectionTag->setParameters(['from' => 'music'])->index());
        $this->assertInstanceOf(SupportCollection::class, $this->collectionTag->setParameters(['in' => 'music'])->index());
        $this->assertInstanceOf(SupportCollection::class, $this->collectionTag->setParameters(['folder' => 'music'])->index());
        $this->assertInstanceOf(SupportCollection::class, $this->collectionTag->setParameters(['use' => 'music'])->index());
        $this->assertInstanceOf(SupportCollection::class, $this->collectionTag->setParameters(['collection' => 'music'])->index());
    }

    #[Test]
    public function it_orders_using_the_collection_sort_direction()
    {
        $this->foods->sortField('title')->sortDirection('asc')->save();

        $this->makeEntry($this->foods, 'b')->set('title', 'Banana')->save();
        $this->makeEntry($this->foods, 'a')->set('title', 'Apple')->save();
        $this->makeEntry($this->foods, 'd')->set('title', 'Danish')->save();
        $this->makeEntry($this->foods, 'c')->set('title', 'Carrot')->save();

        $this->setTagParameters(['in' => 'foods']);

        $items = collect($this->collectionTag->index()->toAugmentedArray())->mapWithKeys(function ($item) {
            return [$item['slug']->value() => $item['title']->value()];
        })->all();

        $this->assertEquals([
            'a' => 'Apple',
            'b' => 'Banana',
            'c' => 'Carrot',
            'd' => 'Danish',
        ], $items);

        $this->foods->sortField('title')->sortDirection('desc')->save();

        $items = collect($this->collectionTag->index()->toAugmentedArray())->mapWithKeys(function ($item) {
            return [$item['slug']->value() => $item['title']->value()];
        })->all();

        $this->assertEquals([
            'd' => 'Danish',
            'c' => 'Carrot',
            'b' => 'Banana',
            'a' => 'Apple',
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

    protected function makeStructure($tree, $collection)
    {
        $structure = (new CollectionStructure)->handle($collection);
        $structure->save();

        $structure->makeTree('en')->tree($tree)->save();

        return $structure;
    }
}
