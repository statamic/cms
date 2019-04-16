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
    }
}
