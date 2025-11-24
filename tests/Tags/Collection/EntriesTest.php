<?php

namespace Tests\Tags\Collection;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Fields\Value;
use Statamic\Query\Scopes\Scope;
use Statamic\Structures\CollectionStructure;
use Statamic\Tags\Collection\Entries;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Statamic\Taxonomies\TermCollection;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $collection;

    public function setUp(): void
    {
        parent::setUp();

        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();
        $this->collection = Facades\Collection::make('test')->taxonomies(['tags', 'categories'])->save();

        app('statamic.scopes')[PostType::handle()] = PostType::class;
        app('statamic.scopes')[PostAnimal::handle()] = PostAnimal::class;

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://localhost/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://localhost/fr/'],
        ]);
    }

    protected function makeEntry($slug)
    {
        return EntryFactory::id($slug)->slug($slug)->collection($this->collection)->make();
    }

    protected function getEntries($params = [])
    {
        $params['from'] = 'test';

        $params = Parameters::make($params, new Context);

        $entries = (new Entries($params))->get();

        // If paginated result set...
        if (method_exists($entries, 'items')) {
            $entries = $entries->items();
        }

        return $entries;
    }

    protected function getEntryIds($params = [])
    {
        return collect($this->getEntries($params))->map->id()->all();
    }

    #[Test]
    public function it_gets_entries_in_a_collection()
    {
        $this->assertCount(0, $this->getEntries());

        $this->makeEntry('test')->save();

        $this->assertCount(1, $this->getEntries());
    }

    #[Test]
    public function it_gets_paginated_entries_in_a_collection()
    {
        $this->makeEntry('a')->save();
        $this->makeEntry('b')->save();
        $this->makeEntry('c')->save();
        $this->makeEntry('d')->save();
        $this->makeEntry('e')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['paginate' => 3])); // recommended v3 style
        $this->assertCount(4, $this->getEntries(['paginate' => true, 'limit' => 4])); // v2 style pagination limiting
        $this->assertCount(5, $this->getEntries(['paginate' => true])); // ignore if no perPage set

        $this->assertEquals(['a', 'b', 'c'], $this->getEntryIds(['paginate' => 3]));

        Paginator::currentPageResolver(function () {
            return 2;
        });

        $this->assertEquals(['d', 'e'], $this->getEntryIds(['paginate' => 3]));
    }

    #[Test]
    public function it_gets_offset_paginated_entries_in_a_collection()
    {
        $this->makeEntry('a')->save();
        $this->makeEntry('b')->save();
        $this->makeEntry('c')->save();
        $this->makeEntry('d')->save();
        $this->makeEntry('e')->save();
        $this->makeEntry('f')->save();
        $this->makeEntry('g')->save();

        $this->assertEquals(['c', 'd', 'e'], $this->getEntryIds(['paginate' => 3, 'offset' => 2]));

        Paginator::currentPageResolver(function () {
            return 2;
        });

        $this->assertEquals(['f', 'g'], $this->getEntryIds(['paginate' => 3, 'offset' => 2]));
    }

    #[Test]
    public function it_should_throw_exception_if_trying_to_paginate_and_limit_at_same_time()
    {
        $this->makeEntry('a')->save();
        $this->makeEntry('b')->save();
        $this->makeEntry('c')->save();
        $this->makeEntry('d')->save();
        $this->makeEntry('e')->save();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot use [paginate] integer in combination with [limit] param.');

        $this->assertCount(3, $this->getEntries(['paginate' => 3, 'limit' => 4]));
    }

    #[Test]
    public function it_should_not_throw_exception_if_trying_to_paginate_or_limit_and_the_other_is_null()
    {
        $this->makeEntry('a')->save();
        $this->makeEntry('b')->save();
        $this->makeEntry('c')->save();
        $this->makeEntry('d')->save();
        $this->makeEntry('e')->save();

        $this->assertCount(3, $this->getEntries(['paginate' => 3, 'limit' => null]));
        $this->assertCount(3, $this->getEntries(['paginate' => null, 'limit' => 3]));
    }

    #[Test]
    public function it_should_throw_exception_if_trying_to_paginate_and_chunk_at_same_time()
    {
        $this->makeEntry('a')->save();
        $this->makeEntry('b')->save();
        $this->makeEntry('c')->save();
        $this->makeEntry('d')->save();
        $this->makeEntry('e')->save();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot use [paginate] in combination with [chunk] param.');

        $this->assertCount(3, $this->getEntries(['paginate' => true, 'chunk' => 2]));
    }

    #[Test]
    public function it_should_throw_exception_if_trying_to_paginate_with_integer_and_chunk_at_same_time()
    {
        $this->makeEntry('a')->save();
        $this->makeEntry('b')->save();
        $this->makeEntry('c')->save();
        $this->makeEntry('d')->save();
        $this->makeEntry('e')->save();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot use [paginate] in combination with [chunk] param.');

        $this->assertCount(3, $this->getEntries(['paginate' => 3, 'chunk' => 2]));
    }

    #[Test]
    public function it_gets_localized_site_entries_in_a_collection()
    {
        Event::fake();

        $this->collection->sites(['en', 'fr'])->save();

        $this->makeEntry('one')->set('title', 'One')->save();
        $this->makeEntry('two')->set('title', 'Two')->save();
        $this->makeEntry('three')->set('title', 'Three')->save();
        $this->makeEntry('four')->set('title', 'Quatre')->locale('fr')->save();
        $this->makeEntry('five')->set('title', 'Cinq')->locale('fr')->save();

        $this->assertCount(3, $this->getEntries(['site' => 'en']));
        $this->assertCount(2, $this->getEntries(['site' => 'fr']));
        $this->assertCount(5, $this->getEntries(['site' => '*']));

        Site::setCurrent('en');
        $this->assertCount(3, $this->getEntries());
        Site::setCurrent('fr');
        $this->assertCount(2, $this->getEntries());
    }

    #[Test]
    public function it_limits_entries_with_offset()
    {
        $this->makeEntry('a')->set('title', 'A')->save();
        $this->makeEntry('b')->set('title', 'B')->save();
        $this->makeEntry('c')->set('title', 'C')->save();
        $this->makeEntry('d')->set('title', 'D')->save();
        $this->makeEntry('e')->set('title', 'E')->save();

        $this->assertCount(5, $this->getEntries());

        $this->assertEquals(
            ['A', 'B', 'C'],
            $this->getEntries(['limit' => 3])->map->get('title')->values()->all()
        );

        $this->assertEquals(
            ['B', 'C', 'D'],
            $this->getEntries(['limit' => 3, 'offset' => 1])->map->get('title')->values()->all()
        );
    }

    #[Test]
    public function it_limits_entries_with_offset_using_value_objects()
    {
        $this->makeEntry('a')->set('title', 'A')->save();
        $this->makeEntry('b')->set('title', 'B')->save();
        $this->makeEntry('c')->set('title', 'C')->save();
        $this->makeEntry('d')->set('title', 'D')->save();
        $this->makeEntry('e')->set('title', 'E')->save();

        $this->assertCount(5, $this->getEntries());

        $this->assertEquals(
            ['A', 'B', 'C'],
            $this->getEntries(['limit' => new Value(3)])->map->get('title')->values()->all()
        );

        $this->assertEquals(
            ['B', 'C', 'D'],
            $this->getEntries(['limit' => 3, 'offset' => new Value(1)])->map->get('title')->values()->all()
        );
    }

    #[Test]
    public function it_filters_by_future_and_past()
    {
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00:12'));

        $this->collection->dated(true)->save();
        $blueprint = Blueprint::makeFromFields(['date' => ['type' => 'date', 'time_enabled' => true, 'time_seconds_enabled' => true]])->setHandle('test');
        Blueprint::shouldReceive('in')->with('collections/test')->once()->andReturn(collect([$blueprint]));

        $this->makeEntry('a')->date('2019-03-09')->save(); // definitely in past
        $this->makeEntry('b')->date('2019-03-10')->save(); // today
        $this->makeEntry('c')->date('2019-03-10-1259')->save(); // today, but before "now"
        $this->makeEntry('d')->date('2019-03-10-1300')->save(); // today, same minute, but before "now"
        $this->makeEntry('e')->date('2019-03-10-130012')->save(); // today, and also "now"
        $this->makeEntry('f')->date('2019-03-10-130015')->save(); // today, same minute, but after "now"
        $this->makeEntry('g')->date('2019-03-10-1301')->save(); // today, but after "now"
        $this->makeEntry('h')->date('2019-03-11')->save(); // definitely in future

        // Default date behaviors.
        $this->assertCount(8, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['show_future' => false]));
        $this->assertCount(8, $this->getEntries(['show_future' => true]));
        $this->assertCount(8, $this->getEntries(['show_past' => true]));
        $this->assertCount(3, $this->getEntries(['show_past' => false]));
        $this->assertCount(3, $this->getEntries(['show_past' => false, 'show_future' => true]));

        // Only future
        $this->collection->dated(true)->futureDateBehavior('public')->pastDateBehavior('unlisted')->save();
        $this->assertCount(3, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['show_future' => false]));
        $this->assertCount(3, $this->getEntries(['show_future' => true]));
        $this->assertCount(8, $this->getEntries(['show_past' => true]));
        $this->assertCount(3, $this->getEntries(['show_past' => false]));
        $this->assertCount(3, $this->getEntries(['show_past' => false, 'show_future' => true]));

        $this->collection->dated(true)->futureDateBehavior('public')->pastDateBehavior('private')->save();
        $this->assertCount(3, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['show_future' => false]));
        $this->assertCount(3, $this->getEntries(['show_future' => true]));
        $this->assertCount(8, $this->getEntries(['show_past' => true]));
        $this->assertCount(3, $this->getEntries(['show_past' => false]));
        $this->assertCount(3, $this->getEntries(['show_past' => false, 'show_future' => true]));

        // Only past
        $this->collection->dated(true)->futureDateBehavior('unlisted')->pastDateBehavior('public')->save();
        $this->assertCount(4, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['show_future' => false]));
        $this->assertCount(8, $this->getEntries(['show_future' => true]));
        $this->assertCount(4, $this->getEntries(['show_past' => true]));
        $this->assertCount(0, $this->getEntries(['show_past' => false]));
        $this->assertCount(3, $this->getEntries(['show_past' => false, 'show_future' => true]));

        $this->collection->dated(true)->futureDateBehavior('private')->pastDateBehavior('public')->save();
        $this->assertCount(4, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['show_future' => false]));
        $this->assertCount(8, $this->getEntries(['show_future' => true]));
        $this->assertCount(4, $this->getEntries(['show_past' => true]));
        $this->assertCount(0, $this->getEntries(['show_past' => false]));
        $this->assertCount(3, $this->getEntries(['show_past' => false, 'show_future' => true]));
    }

    #[Test]
    public function it_filters_by_since_and_until()
    {
        $this->collection->dated(true)->save();
        $blueprint = Blueprint::makeFromFields(['date' => ['type' => 'date', 'time_enabled' => true, 'time_seconds_enabled' => true]])->setHandle('test');
        Blueprint::shouldReceive('in')->with('collections/test')->once()->andReturn(collect([$blueprint]));

        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00:12'));

        $this->makeEntry('a')->date('2019-03-06')->save(); // further in past
        $this->makeEntry('b')->date('2019-03-09')->save(); // yesterday
        $this->makeEntry('c')->date('2019-03-10')->save(); // today
        $this->makeEntry('d')->date('2019-03-10-1259')->save(); // today, but before "now"
        $this->makeEntry('e')->date('2019-03-10-1300')->save(); // today, same minute, but before "now"
        $this->makeEntry('f')->date('2019-03-10-130012')->save(); // today, and also "now"
        $this->makeEntry('g')->date('2019-03-10-130015')->save(); // today, same minute, but after "now"
        $this->makeEntry('h')->date('2019-03-10-1301')->save(); // today, but after "now"
        $this->makeEntry('i')->date('2019-03-11')->save(); // tomorrow
        $this->makeEntry('j')->date('2019-03-13')->save(); // further in future

        $this->assertCount(10, $this->getEntries(['show_future' => true]));
        $this->assertCount(8, $this->getEntries(['show_future' => true, 'since' => 'yesterday']));
        $this->assertCount(9, $this->getEntries(['show_future' => true, 'since' => '-2 days']));
        $this->assertCount(5, $this->getEntries(['show_future' => true, 'until' => 'now']));
        $this->assertCount(8, $this->getEntries(['show_future' => true, 'until' => 'tomorrow']));
    }

    #[Test]
    public function it_filters_by_status()
    {
        $this->collection->dated(true)->futureDateBehavior('private')->pastDateBehavior('public')->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00:12'));

        $this->makeEntry('a')->date('2019-03-08')->published(true)->save(); // definitely in past
        $this->makeEntry('b')->date('2019-03-09')->published(false)->save(); // definitely in past
        $this->makeEntry('c')->date('2019-03-10')->published(false)->save(); // today
        $this->makeEntry('d')->date('2019-03-11')->published(true)->save(); // definitely in future, so status will not be 'published'

        $this->assertCount(1, $this->getEntries()); // defaults to 'published'
        $this->assertCount(1, $this->getEntries(['status:is' => 'published']));
        $this->assertCount(3, $this->getEntries(['status:not' => 'published']));
        $this->assertCount(3, $this->getEntries(['status:in' => 'published|draft']));
        $this->assertCount(4, $this->getEntries(['status:is' => 'any']));
    }

    #[Test]
    public function it_filters_by_published_boolean()
    {
        $this->collection->dated(true)->futureDateBehavior('private')->pastDateBehavior('public')->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00:12'));

        $this->makeEntry('a')->date('2019-03-08')->published(true)->save(); // definitely in past
        $this->makeEntry('b')->date('2019-03-09')->published(false)->save(); // definitely in past
        $this->makeEntry('c')->date('2019-03-10')->published(false)->save(); // today
        $this->makeEntry('d')->date('2019-03-11')->published(true)->save(); // definitely in future, so status will not be 'published'

        $this->assertCount(1, $this->getEntries()); // defaults to 'published'
        $this->assertCount(1, $this->getEntries(['published:is' => true]));
        $this->assertCount(2, $this->getEntries(['published:not' => true]));
    }

    #[Test]
    public function it_filters_by_custom_query_scopes()
    {
        $this->makeEntry('a')->set('title', 'Cat Stories')->save();
        $this->makeEntry('b')->set('title', 'Tiger Stories')->save();
        $this->makeEntry('c')->set('title', 'Tiger Fables')->save();
        $this->makeEntry('d')->set('title', 'Tiger Tales')->save();

        $this->assertCount(4, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['query_scope' => 'post_type', 'post_type' => 'stories']));
        $this->assertCount(2, $this->getEntries(['filter' => 'post_type', 'post_type' => 'stories']));
        $this->assertCount(3, $this->getEntries(['query_scope' => 'post_animal', 'post_animal' => 'tiger']));
        $this->assertCount(3, $this->getEntries(['filter' => 'post_animal', 'post_animal' => 'tiger']));

        $this->assertCount(1, $this->getEntries([
            'query_scope' => 'post_type|post_animal',
            'post_type' => 'stories',
            'post_animal' => 'tiger',
        ]));
    }

    #[Test]
    public function it_sorts_entries()
    {
        $this->collection->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00:12'));

        $this->makeEntry('a')->date('2019-02-06')->set('title', 'Pear')->save();
        $this->makeEntry('b')->date('2019-02-07')->set('title', 'Apple')->save();
        $this->makeEntry('c')->date('2019-03-03')->set('title', 'Banana')->save();

        $this->assertEquals(
            ['2019-03-03', '2019-02-07', '2019-02-06'],
            $this->getEntries(['sort' => 'date:desc'])->map->date()->map->format('Y-m-d')->all()
        );

        $this->assertEquals(
            ['Apple', 'Banana', 'Pear'],
            $this->getEntries(['sort' => 'title'])->map->get('title')->all()
        );

        $this->assertEquals(
            ['Pear', 'Banana', 'Apple'],
            $this->getEntries(['order_by' => 'title:desc'])->map->get('title')->all()
        );
    }

    #[Test]
    public function it_sorts_entries_by_multiple_columns()
    {
        $this->collection->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00:12'));

        $this->makeEntry('a')->date('2019-02-06')->set('title', 'Pear')->save();
        $this->makeEntry('b')->date('2019-02-06')->set('title', 'Apple')->save();
        $this->makeEntry('c')->date('2019-03-03')->set('title', 'Apricot')->save();
        $this->makeEntry('d')->date('2019-03-03')->set('title', 'Banana')->save();

        $this->assertEquals(
            ['Apricot', 'Banana', 'Apple', 'Pear'],
            $this->getEntries(['sort' => 'date:desc|title'])->map->get('title')->all()
        );

        $this->assertEquals(
            ['Banana', 'Apricot', 'Pear', 'Apple'],
            $this->getEntries(['sort' => 'date:desc|title:desc'])->map->get('title')->all()
        );
    }

    #[Test]
    public function it_sorts_entries_randomly()
    {
        $this->makeEntry('a')->set('number', '1')->save();
        $this->makeEntry('b')->set('number', '2')->save();
        $this->makeEntry('c')->set('number', '3')->save();

        $orders = collect();

        for ($i = 0; $i < 10; $i++) {
            $orders[] = $this->getEntries(['sort' => 'random'])->map->get('number')->implode('');
        }

        $this->assertTrue($orders->unique()->count() > 1);
    }

    #[Test]
    public function it_can_sort_a_nested_structured_collection()
    {
        $this->makeEntry('a')->save();
        $this->makeEntry('b')->save();
        $this->makeEntry('b1')->save();
        $this->makeEntry('b2')->save();
        $this->makeEntry('c')->save();
        $this->makeEntry('c1')->save();
        $this->makeEntry('c2')->save();

        $structure = (new CollectionStructure)->maxDepth(1);
        $this->collection->structure($structure)->save();
        $structure->makeTree('en')->tree([
            ['entry' => 'b', 'children' => [
                ['entry' => 'b1'],
                ['entry' => 'b2'],
            ]],
            ['entry' => 'c', 'children' => [
                ['entry' => 'c2'],
                ['entry' => 'c1'],
            ]],
            ['entry' => 'a'],
        ])->save();

        $this->assertEquals(['a', 'b', 'b1', 'b2', 'c', 'c1', 'c2'], $this->getEntries(['sort' => 'id'])->map->id()->all());
        $this->assertEquals(['b', 'b1', 'b2', 'c', 'c2', 'c1', 'a'], $this->getEntries(['sort' => 'order|title'])->map->id()->all());
    }

    #[Test]
    public function it_can_sort_a_linear_structured_collection()
    {
        $this->makeEntry('a')->save();
        $this->makeEntry('b')->save();
        $this->makeEntry('c')->save();

        $structure = (new CollectionStructure)->maxDepth(1);
        $this->collection->structure($structure)->save();
        $structure->makeTree('en')->tree([
            ['entry' => 'b'],
            ['entry' => 'c'],
            ['entry' => 'a'],
        ])->save();

        $this->assertEquals(['a', 'b', 'c'], $this->getEntries(['sort' => 'id'])->map->id()->all());
        $this->assertEquals(['b', 'c', 'a'], $this->getEntries(['sort' => 'order|title'])->map->id()->all());
    }

    #[Test]
    public function it_filters_by_taxonomy_terms()
    {
        $this->makeEntry('1')->data(['tags' => ['rad'], 'categories' => ['news']])->save();
        $this->makeEntry('2')->data(['tags' => ['awesome'], 'categories' => ['events']])->save();
        $this->makeEntry('3')->data(['tags' => ['rad', 'awesome']])->save();
        $this->makeEntry('4')->data(['tags' => ['meh']])->save();
        $this->makeEntry('5')->data([])->save();

        // Where term in
        $this->assertEquals([1, 3], $this->getEntryIds(['taxonomy:tags:in' => 'rad']));
        $this->assertEquals([1, 3], $this->getEntryIds(['taxonomy:tags:any' => 'rad']));
        $this->assertEquals([1, 3], $this->getEntryIds(['taxonomy:tags' => 'rad'])); // shorthand

        // Where any of these terms in
        $this->assertEquals([1, 3, 4], $this->getEntryIds(['taxonomy:tags:in' => 'rad|meh']));
        $this->assertEquals([1, 3, 4], $this->getEntryIds(['taxonomy:tags:in' => ['rad', 'meh']]));
        $this->assertEquals([1, 3, 4], $this->getEntryIds(['taxonomy:tags:any' => 'rad|meh']));
        $this->assertEquals([1, 3, 4], $this->getEntryIds(['taxonomy:tags:any' => ['rad', 'meh']]));
        $this->assertEquals([1, 3, 4], $this->getEntryIds(['taxonomy:tags' => 'rad|meh'])); // shorthand
        $this->assertEquals([1, 3, 4], $this->getEntryIds(['taxonomy:tags' => ['rad', 'meh']])); // shorthand

        // Where term not in
        $this->assertEquals([2, 4, 5], $this->getEntryIds(['taxonomy:tags:not_in' => 'rad']));
        $this->assertEquals([2, 4, 5], $this->getEntryIds(['taxonomy:tags:not' => 'rad']));

        // Where terms not in
        $this->assertEquals([4, 5], $this->getEntryIds(['taxonomy:tags:not_in' => 'rad|awesome']));
        $this->assertEquals([4, 5], $this->getEntryIds(['taxonomy:tags:not_in' => ['rad', 'awesome']]));
        $this->assertEquals([4, 5], $this->getEntryIds(['taxonomy:tags:not' => 'rad|awesome']));
        $this->assertEquals([4, 5], $this->getEntryIds(['taxonomy:tags:not' => ['rad', 'awesome']]));

        // Where all of these terms in
        $this->assertEquals([3], $this->getEntryIds(['taxonomy:tags:all' => 'rad|awesome']));
        $this->assertEquals([3], $this->getEntryIds(['taxonomy:tags:all' => ['rad', 'awesome']]));

        // Ensure in and not logic intersect properly
        $this->assertEquals([1, 3], $this->getEntryIds(['taxonomy:tags:in' => 'rad|meh', 'taxonomy:tags:not' => 'meh']));

        // Ensure in logic intersects properly across multiple taxonomies
        $this->assertEquals([1], $this->getEntryIds(['taxonomy:tags:in' => 'rad|meh', 'taxonomy:categories:in' => 'news']));

        // Passing IDs into generic taxonomy param
        $this->assertEquals([1, 3], $this->getEntryIds(['taxonomy' => 'tags::rad']));
        $this->assertEquals([1, 3, 4], $this->getEntryIds(['taxonomy' => 'tags::rad|tags::meh']));
        $this->assertEquals([1, 3, 4], $this->getEntryIds(['taxonomy' => 'tags::rad|tags::meh|categories::news']));
        $this->assertEquals([1], $this->getEntryIds(['taxonomy::all' => 'tags::rad|categories::news'])); // modifier still expected to be 3rd segment
        $this->assertEquals([1], $this->getEntryIds(['taxonomy' => 'tags::rad|tags::meh', 'taxonomy:categories' => 'news'])); // mix and match

        // Ensure it works when passing terms (eg from a term fieldtype)
        $this->assertEquals([1, 3, 4], $this->getEntryIds(['taxonomy:tags:in' => Term::query()->whereIn('slug', ['rad', 'meh'])->get()]));
        $this->assertEquals([1, 3], $this->getEntryIds(['taxonomy:tags:in' => Term::find('tags::rad')]));
    }

    #[Test]
    public function it_throws_an_exception_when_using_an_unknown_taxonomy_query_modifier()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown taxonomy query modifier [xyz]. Valid values are "any", "not", and "all".');

        $this->getEntries(['taxonomy:tags:xyz' => 'test']);
    }

    #[Test]
    public function it_returns_all_entries_where_taxonomy_parameter_value_is_empty()
    {
        $this->makeEntry('1')->save();
        $this->makeEntry('2')->data(['tags' => ['rad']])->save();
        $this->makeEntry('3')->data(['tags' => ['meh']])->save();

        $this->assertEquals([1, 2, 3], $this->getEntries(['taxonomy:tags' => ''])->map->slug()->all());
        $this->assertEquals([1, 2, 3], $this->getEntries(['taxonomy:tags' => '|'])->map->slug()->all());
        $this->assertEquals([1, 2, 3], $this->getEntries(['taxonomy:tags' => []])->map->slug()->all());
    }

    #[Test]
    public function it_accepts_a_term_collection_to_filter_by_taxonomy()
    {
        $this->makeEntry('1')->data(['tags' => ['rad']])->save();
        $this->makeEntry('2')->data(['tags' => ['rad']])->save();
        $this->makeEntry('3')->data(['tags' => ['meh']])->save();

        $this->assertEquals([1, 2], $this->getEntries(['taxonomy:tags' => TermCollection::make([Term::make('rad')->taxonomy('tags')])])->map->slug()->all());
    }

    #[Test]
    public function it_accepts_a_query_builder_to_filter_by_taxonomy()
    {
        $this->makeEntry('1')->data(['tags' => ['rad'], 'categories' => ['news']])->save();
        $this->makeEntry('2')->data(['tags' => ['awesome'], 'categories' => ['events']])->save();
        $this->makeEntry('3')->data(['tags' => ['rad', 'awesome']])->save();
        $this->makeEntry('4')->data(['tags' => ['meh']])->save();

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn(TermCollection::make([
            tap(Term::make('rad')->taxonomy('tags')->dataForLocale('en', []))->save(),
            tap(Term::make('awesome')->taxonomy('tags')->dataForLocale('en', []))->save(),
        ]));

        $this->assertEquals([3], $this->getEntries(['taxonomy:tags:all' => $builder])->map->slug()->all());
    }

    #[Test]
    public function it_chunks_entries()
    {
        $this->makeEntry('1')->save();
        $this->makeEntry('2')->save();
        $this->makeEntry('3')->save();

        $entries = $this->getEntries(['chunk' => 2]);

        $this->assertEquals([1, 2], $entries->first()->map->slug()->all());
        $this->assertEquals([3], $entries->last()->map->slug()->all());
    }
}

class PostType extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', 'like', "%{$params['post_type']}%");
    }
}

class PostAnimal extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', 'like', "%{$params['post_animal']}%");
    }
}
