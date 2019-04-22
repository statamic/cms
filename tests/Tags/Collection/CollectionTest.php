<?php

namespace Tests\Tags\Collection;

use Statamic\API;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use Statamic\Tags\Collection\Entries;
use Statamic\Tags\Collection\Collection;
use Tests\PreventSavingStacheItemsToDisk;

class CollectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    function setUp(): void
    {
        parent::setUp();

        $this->music = API\Collection::make('music')->save();
        $this->art = API\Collection::make('art')->save();
        $this->collectionTag = new Collection;
    }

    protected function makeEntry($collection)
    {
        $entry = API\Entry::make()->collection($collection);

        return $entry->makeAndAddLocalization('en', function ($loc) { });
    }

    /** @test */
    function it_gets_entries_from_multiple_collections()
    {
        $this->makeEntry($this->music)->save();
        $this->makeEntry($this->art)->save();

        $this->collectionTag->parameters = [
            'from' => 'music|art'
        ];

        $this->assertCount(2, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_multiple_collections_using_params()
    {
        $this->makeEntry($this->music)->set('title', 'I Love Guitars')->save();
        $this->makeEntry($this->music)->set('title', 'I Love Drums')->save();
        $this->makeEntry($this->music)->set('title', 'I Hate Flutes')->save();
        $this->makeEntry($this->art)->set('title', 'I Love Drawing')->save();
        $this->makeEntry($this->art)->set('title', 'I Love Painting')->save();
        $this->makeEntry($this->art)->set('title', 'I Hate Sculpting')->save();

        $this->collectionTag->parameters = [
            'from' => 'music|art',
            'title:contains' => 'love'
        ];

        $this->assertCount(4, $this->collectionTag->index());
    }
}
