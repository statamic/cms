<?php

namespace Tests\Tags\Collection;

use Statamic\API;
use Tests\TestCase;
use Statamic\Tags\Collection\Entries;
use Tests\PreventSavingStacheItemsToDisk;
use Illuminate\Support\Carbon;

class HasConditionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    function setUp(): void
    {
        parent::setUp();
        $this->collection = API\Collection::make('test')->save();
    }

    protected function makeEntry()
    {
        $entry = API\Entry::make()->collection($this->collection);
        return $entry->makeAndAddLocalization('en', function ($loc) { });
    }

    protected function getEntries($params = [])
    {
        return (new Entries('test', $params))->get();
    }

    /** @test */
    function it_filters_by_is_condition()
    {
        $this->makeEntry()->set('title', 'Dog')->save();
        $this->makeEntry()->set('title', 'Cat')->save();

        $this->assertCount(2, $this->getEntries());

        $this->assertCount(1, $this->getEntries(['title:is' => 'Dog']));
        $this->assertCount(1, $this->getEntries(['title:equals' => 'Dog']));

        $this->assertEquals('Dog', $this->getEntries(['title:is' => 'Dog'])->first()->get('title'));
        $this->assertEquals('Dog', $this->getEntries(['title:equals' => 'Dog'])->first()->get('title'));
    }

    /** @test */
    function it_filters_by_not_condition()
    {
        $this->makeEntry()->set('title', 'Dog')->save();
        $this->makeEntry()->set('title', 'Cat')->save();

        $this->assertCount(2, $this->getEntries());

        $this->assertCount(1, $this->getEntries(['title:not' => 'Dog']));
        $this->assertCount(1, $this->getEntries(['title:isnt' => 'Dog']));
        $this->assertCount(1, $this->getEntries(['title:aint' => 'Dog']));
        $this->assertCount(1, $this->getEntries(['title:¯\\_(ツ)_/¯' => 'Dog']));

        $this->assertEquals('Cat', $this->getEntries(['title:not' => 'Dog'])->first()->get('title'));
        $this->assertEquals('Cat', $this->getEntries(['title:isnt' => 'Dog'])->first()->get('title'));
        $this->assertEquals('Cat', $this->getEntries(['title:aint' => 'Dog'])->first()->get('title'));
        $this->assertEquals('Cat', $this->getEntries(['title:¯\\_(ツ)_/¯' => 'Dog'])->first()->get('title'));
    }

    /** @test */
    function it_filters_by_contains_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();

        $this->assertCount(2, $this->getEntries());
        $this->assertCount(1, $this->getEntries(['title:contains' => 'Sto']));
        $this->assertEquals('Dog Stories', $this->getEntries(['title:contains' => 'Sto'])->first()->get('title'));
    }

    /** @test */
    function it_filters_by_doesnt_contain_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();

        $this->assertCount(2, $this->getEntries());
        $this->assertCount(1, $this->getEntries(['title:doesnt_contain' => 'Sto']));
        $this->assertEquals('Cat Fables', $this->getEntries(['title:doesnt_contain' => 'Sto'])->first()->get('title'));
    }

    /** @test */
    function it_filters_by_starts_with_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();

        $this->assertCount(2, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['title:starts_with' => 'Sto']));
        $this->assertCount(0, $this->getEntries(['title:begins_with' => 'Sto']));
        $this->assertCount(1, $this->getEntries(['title:starts_with' => 'Dog']));
        $this->assertCount(1, $this->getEntries(['title:begins_with' => 'Dog']));

        $this->assertEquals('Dog Stories', $this->getEntries(['title:starts_with' => 'Dog'])->first()->get('title'));
        $this->assertEquals('Dog Stories', $this->getEntries(['title:begins_with' => 'Dog'])->first()->get('title'));
    }

    /** @test */
    function it_filters_by_doesnt_start_with_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();

        $this->assertCount(2, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['title:doesnt_start_with' => 'Sto']));
        $this->assertCount(2, $this->getEntries(['title:doesnt_begin_with' => 'Sto']));
        $this->assertCount(1, $this->getEntries(['title:doesnt_start_with' => 'Dog']));
        $this->assertCount(1, $this->getEntries(['title:doesnt_begin_with' => 'Dog']));

        $this->assertEquals('Cat Fables', $this->getEntries(['title:doesnt_start_with' => 'Dog'])->first()->get('title'));
        $this->assertEquals('Cat Fables', $this->getEntries(['title:doesnt_begin_with' => 'Dog'])->first()->get('title'));
    }

    /** @test */
    function it_filters_by_ends_with_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();

        $this->assertCount(2, $this->getEntries());
        $this->assertCount(0, $this->getEntries(['title:ends_with' => 'Sto']));
        $this->assertCount(1, $this->getEntries(['title:ends_with' => 'Stories']));
        $this->assertEquals('Dog Stories', $this->getEntries(['title:ends_with' => 'Stories'])->first()->get('title'));
    }

    /** @test */
    function it_filters_by_doesnt_end_with_condition()
    {
        $this->makeEntry()->set('title', 'Dog Stories')->save();
        $this->makeEntry()->set('title', 'Cat Fables')->save();

        $this->assertCount(2, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['title:doesnt_end_with' => 'Sto']));
        $this->assertCount(1, $this->getEntries(['title:doesnt_end_with' => 'Stories']));
        $this->assertEquals('Cat Fables', $this->getEntries(['title:doesnt_end_with' => 'Stories'])->first()->get('title'));
    }

    /** @test */
    function it_filters_by_greater_than_condition()
    {
        $this->makeEntry()->set('age', 11)->save();
        $this->makeEntry()->set('age', '11')->save();
        $this->makeEntry()->set('age', 21)->save();
        $this->makeEntry()->set('age', '21')->save();
        $this->makeEntry()->set('age', 24)->save();
        $this->makeEntry()->set('age', '24')->save();

        $this->assertCount(6, $this->getEntries());
        $this->assertCount(4, $this->getEntries(['age:greater_than' => '18']));
        $this->assertCount(4, $this->getEntries(['age:greater_than' => 18]));
        $this->assertCount(4, $this->getEntries(['age:gt' => '18']));
        $this->assertCount(4, $this->getEntries(['age:gt' => 18]));
    }

    /** @test */
    function it_filters_by_less_than_condition()
    {
        $this->makeEntry()->set('age', 11)->save();
        $this->makeEntry()->set('age', '11')->save();
        $this->makeEntry()->set('age', 21)->save();
        $this->makeEntry()->set('age', '21')->save();
        $this->makeEntry()->set('age', 24)->save();
        $this->makeEntry()->set('age', '24')->save();

        $this->assertCount(6, $this->getEntries());
        $this->assertCount(2, $this->getEntries(['age:less_than' => '18']));
        $this->assertCount(2, $this->getEntries(['age:less_than' => 18]));
        $this->assertCount(2, $this->getEntries(['age:lt' => '18']));
        $this->assertCount(2, $this->getEntries(['age:lt' => 18]));
    }
}
