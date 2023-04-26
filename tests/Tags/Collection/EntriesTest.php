<?php

namespace Tests\Tags\Collection;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Mockery;
use Statamic\Contracts\Query\Builder;
use Statamic\Facades;
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
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.sites', [
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://localhost/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://localhost/fr/'],
            ],
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

        return (new Entries($params))->get();
    }

    protected function getEntryIds($params = [])
    {
        return collect($this->getEntries($params)->items())->map->id()->all();
    }

    /** @test */
    public function it_gets_entries_in_a_collection()
    {
        $this->assertCount(0, $this->getEntries());

        $this->makeEntry('test')->save();

        $this->assertCount(1, $this->getEntries());
    }

    /** @test */
    public function it_gets_paginated_entries_in_a_collection()
    {
        $this->makeEntry('a')->save();
        $this->makeEntry('b')->save();
        $this->makeEntry('c')->save();
        $this->makeEntry('d')->save();
        $this->makeEntry('e')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['paginate' => 3])); // recommended v3 style
        $this->assertCount(4, $this->getEntries(['paginate' => true, 'limit' => 4])); // v2 style
        $this->assertCount(3, $this->getEntries(['paginate' => 3, 'limit' => 4])); // precedence test
        $this->assertCount(5, $this->getEntries(['paginate' => true])); // ignore if no perPage set

        $this->assertEquals(['a', 'b', 'c'], $this->getEntryIds(['paginate' => 3]));

        Paginator::currentPageResolver(function () {
            return 2;
        });

        $this->assertEquals(['d', 'e'], $this->getEntryIds(['paginate' => 3]));
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_filters_by_future_and_past()
    {
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry('a')->date('2019-03-09')->save(); // definitely in past
        $this->makeEntry('b')->date('2019-03-10')->save(); // today
        $this->makeEntry('c')->date('2019-03-10-1259')->save(); // today, but before "now"
        $this->makeEntry('d')->date('2019-03-10-1300')->save(); // today, and also "now"
        $this->makeEntry('e')->date('2019-03-10-1301')->save(); // today, but after "now"
        $this->makeEntry('f')->date('2019-03-11')->save(); // definitely in future

        // Default date behaviors.
        $this->collection->dated(true)->save();
        $this->assertCount(6, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['show_future' => false]));
        $this->assertCount(6, $this->getEntries(['show_future' => true]));
        $this->assertCount(6, $this->getEntries(['show_past' => true]));
        $this->assertCount(2, $this->getEntries(['show_past' => false]));
        $this->assertCount(2, $this->getEntries(['show_past' => false, 'show_future' => true]));

        // Only future
        $this->collection->dated(true)->futureDateBehavior('public')->pastDateBehavior('unlisted')->save();
        $this->assertCount(2, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['show_future' => false]));
        $this->assertCount(2, $this->getEntries(['show_future' => true]));
        $this->assertCount(6, $this->getEntries(['show_past' => true]));
        $this->assertCount(2, $this->getEntries(['show_past' => false]));
        $this->assertCount(2, $this->getEntries(['show_past' => false, 'show_future' => true]));

        $this->collection->dated(true)->futureDateBehavior('public')->pastDateBehavior('private')->save();
        $this->assertCount(2, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['show_future' => false]));
        $this->assertCount(2, $this->getEntries(['show_future' => true]));
        $this->assertCount(6, $this->getEntries(['show_past' => true]));
        $this->assertCount(2, $this->getEntries(['show_past' => false]));
        $this->assertCount(2, $this->getEntries(['show_past' => false, 'show_future' => true]));

        // Only past
        $this->collection->dated(true)->futureDateBehavior('unlisted')->pastDateBehavior('public')->save();
        $this->assertCount(3, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['show_future' => false]));
        $this->assertCount(6, $this->getEntries(['show_future' => true]));
        $this->assertCount(3, $this->getEntries(['show_past' => true]));
        $this->assertCount(0, $this->getEntries(['show_past' => false]));
        $this->assertCount(2, $this->getEntries(['show_past' => false, 'show_future' => true]));

        $this->collection->dated(true)->futureDateBehavior('private')->pastDateBehavior('public')->save();
        $this->assertCount(3, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['show_future' => false]));
        $this->assertCount(6, $this->getEntries(['show_future' => true]));
        $this->assertCount(3, $this->getEntries(['show_past' => true]));
        $this->assertCount(0, $this->getEntries(['show_past' => false]));
        $this->assertCount(2, $this->getEntries(['show_past' => false, 'show_future' => true]));
    }

    /** @test */
    public function it_filters_by_since_and_until()
    {
        $this->collection->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry('a')->date('2019-03-06')->save(); // further in past
        $this->makeEntry('b')->date('2019-03-09')->save(); // yesterday
        $this->makeEntry('c')->date('2019-03-10')->save(); // today
        $this->makeEntry('d')->date('2019-03-10-1259')->save(); // today, but before "now"
        $this->makeEntry('e')->date('2019-03-10-1300')->save(); // today, and also "now"
        $this->makeEntry('f')->date('2019-03-10-1301')->save(); // today, but after "now"
        $this->makeEntry('g')->date('2019-03-11')->save(); // tomorrow
        $this->makeEntry('h')->date('2019-03-13')->save(); // further in future

        $this->assertCount(8, $this->getEntries(['show_future' => true]));
        $this->assertCount(6, $this->getEntries(['show_future' => true, 'since' => 'yesterday']));
        $this->assertCount(7, $this->getEntries(['show_future' => true, 'since' => '-2 days']));
        $this->assertCount(4, $this->getEntries(['show_future' => true, 'until' => 'now']));
        $this->assertCount(6, $this->getEntries(['show_future' => true, 'until' => 'tomorrow']));
    }

    /** @test */
    public function it_filters_by_status()
    {
        $this->collection->dated(true)->futureDateBehavior('private')->pastDateBehavior('public')->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry('a')->date('2019-03-08')->published(true)->save(); // definitely in past
        $this->makeEntry('b')->date('2019-03-09')->published(false)->save(); // definitely in past
        $this->makeEntry('c')->date('2019-03-10')->published(false)->save(); // today
        $this->makeEntry('d')->date('2019-03-11')->published(true)->save(); // definitely in future, so status will not be 'published'

        $this->assertCount(1, $this->getEntries()); // defaults to 'published'
        $this->assertCount(1, $this->getEntries(['status:is' => 'published']));
        $this->assertCount(3, $this->getEntries(['status:not' => 'published']));
        $this->assertCount(3, $this->getEntries(['status:in' => 'published|draft']));
    }

    /** @test */
    public function it_filters_by_published_boolean()
    {
        $this->collection->dated(true)->futureDateBehavior('private')->pastDateBehavior('public')->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

        $this->makeEntry('a')->date('2019-03-08')->published(true)->save(); // definitely in past
        $this->makeEntry('b')->date('2019-03-09')->published(false)->save(); // definitely in past
        $this->makeEntry('c')->date('2019-03-10')->published(false)->save(); // today
        $this->makeEntry('d')->date('2019-03-11')->published(true)->save(); // definitely in future, so status will not be 'published'

        $this->assertCount(1, $this->getEntries()); // defaults to 'published'
        $this->assertCount(1, $this->getEntries(['published:is' => true]));
        $this->assertCount(2, $this->getEntries(['published:not' => true]));
    }

    /** @test */
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

    /** @test */
    public function it_sorts_entries()
    {
        $this->collection->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

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

    /** @test */
    public function it_sorts_entries_by_multiple_columns()
    {
        $this->collection->dated(true)->save();
        Carbon::setTestNow(Carbon::parse('2019-03-10 13:00'));

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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_filters_by_a_single_taxonomy_term()
    {
        $this->makeEntry('1')->data(['tags' => ['rad']])->save();
        $this->makeEntry('2')->data(['tags' => ['rad']])->save();
        $this->makeEntry('3')->data(['tags' => ['meh']])->save();

        $this->assertEquals([1, 2], $this->getEntries(['taxonomy:tags' => 'rad'])->map->slug()->all());
        $this->assertEquals([1, 2], $this->getEntries(['taxonomy:tags' => TermCollection::make([Term::make('rad')->taxonomy('tags')])])->map->slug()->all());
    }

    /** @test */
    public function it_filters_out_a_single_taxonomy_term()
    {
        $this->makeEntry('1')->data(['tags' => ['rad']])->save();
        $this->makeEntry('2')->data(['tags' => ['rad']])->save();
        $this->makeEntry('3')->data(['tags' => ['meh']])->save();

        $this->assertEquals([1, 2], $this->getEntries(['taxonomy:tags:not' => 'meh'])->map->slug()->all());
        $this->assertEquals([1, 2], $this->getEntries(['taxonomy:tags:not' => TermCollection::make([Term::make('meh')->taxonomy('tags')])])->map->slug()->all());
    }

    /** @test */
    public function it_filters_out_multiple_taxonomy_terms()
    {
        $this->makeEntry('1')->data(['tags' => ['rad'], 'categories' => ['news']])->save();
        $this->makeEntry('2')->data(['tags' => ['awesome'], 'categories' => ['events']])->save();
        $this->makeEntry('3')->data(['tags' => ['rad', 'awesome']])->save();
        $this->makeEntry('4')->data(['tags' => ['meh']])->save();
        $this->makeEntry('5')->data([])->save();

        $this->assertEquals([4, 5], $this->getEntries(['taxonomy:tags:not' => 'rad|awesome'])->map->slug()->all());
        $this->assertEquals([4, 5], $this->getEntries(['taxonomy:tags:not' => ['rad', 'awesome']])->map->slug()->all());
        $this->assertEquals([2, 5], $this->getEntries(['taxonomy:tags:not' => 'rad|meh'])->map->slug()->all());
        $this->assertEquals([2, 5], $this->getEntries(['taxonomy:tags:not' => ['rad', 'meh']])->map->slug()->all());

        // Ensure `whereIn` and `whereNot` logic intersect results properly.
        $this->assertEquals([1, 3], $this->getEntries(['taxonomy:tags' => ['rad', 'meh'], 'taxonomy:tags:not' => ['meh']])->map->slug()->all());
    }

    /** @test */
    public function it_filters_by_in_multiple_taxonomy_terms()
    {
        $this->makeEntry('1')->data(['tags' => ['rad'], 'categories' => ['news']])->save();
        $this->makeEntry('2')->data(['tags' => ['awesome'], 'categories' => ['events']])->save();
        $this->makeEntry('3')->data(['tags' => ['rad', 'awesome']])->save();
        $this->makeEntry('4')->data(['tags' => ['meh']])->save();

        $this->assertEquals([3], $this->getEntries(['taxonomy:tags:all' => 'rad|awesome'])->map->slug()->all());
        $this->assertEquals([3], $this->getEntries(['taxonomy:tags:all' => ['rad', 'awesome']])->map->slug()->all());
        $this->assertEquals([1], $this->getEntries(['taxonomy:tags' => 'rad', 'taxonomy:categories' => 'news'])->map->slug()->all());
        $this->assertEquals(0, $this->getEntries(['taxonomy:tags' => 'rad', 'taxonomy:categories' => 'events'])->count());
        $this->assertEquals([1, 3, 4], $this->getEntries(['taxonomy:tags' => 'rad|meh'])->map->slug()->all());
        $this->assertEquals([1, 3, 4], $this->getEntries(['taxonomy:tags' => ['rad', 'meh']])->map->slug()->all());
        $this->assertEquals([1, 2, 3], $this->getEntries(['taxonomy' => 'tags::rad|categories::events'])->map->slug()->all());
        $this->assertEquals([1, 2, 3], $this->getEntries(['taxonomy' => ['tags::rad', 'categories::events']])->map->slug()->all());
        $this->assertEquals([3], $this->getEntries(['taxonomy' => 'tags::rad|tags::meh', 'taxonomy:tags' => 'awesome'])->map->slug()->all());
        $this->assertEquals([2], $this->getEntries(['taxonomy' => 'tags::meh|categories::events', 'taxonomy:tags' => 'awesome'])->map->slug()->all());
    }

    /** @test */
    public function it_throws_an_exception_when_using_an_unknown_taxonomy_query_modifier()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown taxonomy query modifier [xyz]. Valid values are "any", "not", and "all".');

        $this->getEntries(['taxonomy:tags:xyz' => 'test']);
    }

    /** @test */
    public function it_returns_all_entries_where_taxonomy_parameter_value_is_empty()
    {
        $this->makeEntry('1')->save();
        $this->makeEntry('2')->data(['tags' => ['rad']])->save();
        $this->makeEntry('3')->data(['tags' => ['meh']])->save();

        $this->assertEquals([1, 2, 3], $this->getEntries(['taxonomy:tags' => ''])->map->slug()->all());
        $this->assertEquals([1, 2, 3], $this->getEntries(['taxonomy:tags' => '|'])->map->slug()->all());
    }

    /** @test */
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
