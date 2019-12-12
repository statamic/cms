<?php

namespace Tests\Tags\Collection;

use Statamic\Facades;
use Tests\TestCase;
use Statamic\Facades\Site;
use Statamic\Facades\Antlers;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Illuminate\Support\Carbon;
use Statamic\Query\Scopes\Scope;
use Statamic\Tags\Collection\Entries;
use Facades\Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;

class EntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    function setUp(): void
    {
        parent::setUp();

        $this->collection = Facades\Collection::make('test')->save();

        app('statamic.scopes')[PostType::handle()] = PostType::class;
        app('statamic.scopes')[PostAnimal::handle()] = PostAnimal::class;
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.sites', [
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://localhost/',],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://localhost/fr/',]
            ]
        ]);
}

    protected function makeEntry($slug)
    {
        return EntryFactory::slug($slug)->collection($this->collection)->make();
    }

    protected function getEntries($params = [])
    {
        $params['from'] = 'test';

        $params = new Parameters($params, new Context([], Antlers::parser()));

        return (new Entries($params))->get();
    }

    /** @test */
    function it_gets_entries_in_a_collection()
    {
        $this->assertCount(0, $this->getEntries());

        $this->makeEntry('test')->save();

        $this->assertCount(1, $this->getEntries());
    }

    /** @test */
    function it_gets_paginated_entries_in_a_collection()
    {
        $this->makeEntry('a')->save();
        $this->makeEntry('b')->save();
        $this->makeEntry('c')->save();
        $this->makeEntry('d')->save();
        $this->makeEntry('e')->save();

        $this->assertCount(5, $this->getEntries());
        $this->assertCount(3, $this->getEntries(['paginate' => 3]));
        $this->assertCount(4, $this->getEntries(['paginate' => true, 'limit' => 4])); // v2 style
        $this->assertCount(3, $this->getEntries(['paginate' => 3, 'limit' => 4])); // precedence
        $this->assertCount(5, $this->getEntries(['paginate' => true])); // ignore if no perPage set
    }

    /** @test */
    function it_gets_localized_site_entries_in_a_collection()
    {
        $this->withoutEvents();

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
    function it_limits_entries_with_offset()
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
    function it_filters_by_publish_status()
    {
        $this->makeEntry('o')->published(true)->save();
        $this->makeEntry('b')->published(true)->save();
        $this->makeEntry('c')->published(false)->save();

        $this->assertCount(2, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['show_unpublished' => false]));
        $this->assertCount(3, $this->getEntries(['show_unpublished' => true]));
        $this->assertCount(2, $this->getEntries(['show_published' => true]));
        $this->assertCount(0, $this->getEntries(['show_published' => false]));
        $this->assertCount(1, $this->getEntries(['show_published' => false, 'show_unpublished' => true]));
    }

    /** @test */
    function it_filters_by_future_and_past()
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
    function it_filters_by_since_and_until()
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
    function it_filters_by_custom_query_scopes()
    {
        $this->makeEntry('a')->set('title', 'Cat Stories')->save();
        $this->makeEntry('b')->set('title', 'Tiger Stories')->save();
        $this->makeEntry('c')->set('title', 'Tiger Fables')->save();
        $this->makeEntry('d')->set('title', 'Tiger Tales')->save();

        $this->assertCount(4, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['query-scope' => 'post_type', 'post_type' => 'stories']));
        $this->assertCount(2, $this->getEntries(['filter' => 'post_type', 'post_type' => 'stories']));
        $this->assertCount(3, $this->getEntries(['query-scope' => 'post_animal', 'post_animal' => 'tiger']));
        $this->assertCount(3, $this->getEntries(['filter' => 'post_animal', 'post_animal' => 'tiger']));

        $this->assertCount(1, $this->getEntries([
            'query-scope' => 'post_type|post_animal',
            'post_type' => 'stories',
            'post_animal' => 'tiger'
        ]));
    }

    /** @test */
    function it_sorts_entries()
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
    function it_sorts_entries_by_multiple_columns()
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
    function it_sorts_entries_randomly()
    {
        $this->makeEntry('a')->set('number', '1')->save();
        $this->makeEntry('b')->set('number', '2')->save();
        $this->makeEntry('c')->set('number', '3')->save();

        $orders = collect();

        for ($i=0; $i < 10; $i++) {
            $orders[] = $this->getEntries(['sort' => 'random'])->map->get('number')->implode('');
        }

        $this->assertTrue($orders->unique()->count() > 1);
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
