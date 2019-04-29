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
        $this->books = API\Collection::make('books')->save();
        $this->collectionTag = new Collection;
    }

    protected function makeEntry($collection)
    {
        $entry = API\Entry::make()->collection($collection);

        return $entry->makeAndAddLocalization('en', function ($loc) { });
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

        $this->collectionTag->parameters = ['from' => 'music|unknown'];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Collection [unknown] does not exist.');

        $this->collectionTag->index();
    }

    /** @test */
    function it_gets_entries_from_multiple_collections()
    {
        $this->makePosts();

        $this->collectionTag->parameters = ['from' => 'music|art'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['in' => 'music|art'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['folder' => 'music|art'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['use' => 'music|art'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['collection' => 'music|art'];
        $this->assertCount(6, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_all_collections()
    {
        $this->makePosts();

        $this->collectionTag->parameters = ['from' => '*'];
        $this->assertCount(9, $this->collectionTag->index());

        $this->collectionTag->parameters = ['in' => '*'];
        $this->assertCount(9, $this->collectionTag->index());

        $this->collectionTag->parameters = ['folder' => '*'];
        $this->assertCount(9, $this->collectionTag->index());

        $this->collectionTag->parameters = ['use' => '*'];
        $this->assertCount(9, $this->collectionTag->index());

        $this->collectionTag->parameters = ['collection' => '*'];
        $this->assertCount(9, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_all_collections_excluding_one()
    {
        $this->makePosts();

        $this->collectionTag->parameters = ['from' => '*', 'not_from' => 'art'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['in' => '*', 'not_in' => 'art'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['folder' => '*', 'not_folder' => 'art'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['use' => '*', 'dont_use' => 'art'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['collection' => '*', 'dont_use' => 'art'];
        $this->assertCount(6, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_multiple_collections_using_params()
    {
        $this->makePosts();

        $this->collectionTag->parameters = ['from' => 'music|art', 'title:contains' => 'love'];
        $this->assertCount(4, $this->collectionTag->index());

        $this->collectionTag->parameters = ['in' => 'music|art', 'title:contains' => 'love'];
        $this->assertCount(4, $this->collectionTag->index());

        $this->collectionTag->parameters = ['folder' => 'music|art', 'title:contains' => 'love'];
        $this->assertCount(4, $this->collectionTag->index());

        $this->collectionTag->parameters = ['use' => 'music|art', 'title:contains' => 'love'];
        $this->assertCount(4, $this->collectionTag->index());

        $this->collectionTag->parameters = ['collection' => 'music|art', 'title:contains' => 'love'];
        $this->assertCount(4, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_all_collections_using_params()
    {
        $this->makePosts();

        $this->collectionTag->parameters = ['from' => '*', 'title:contains' => 'love'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['in' => '*', 'title:contains' => 'love'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['folder' => '*', 'title:contains' => 'love'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['use' => '*', 'title:contains' => 'love'];
        $this->assertCount(6, $this->collectionTag->index());

        $this->collectionTag->parameters = ['collection' => '*', 'title:contains' => 'love'];
        $this->assertCount(6, $this->collectionTag->index());
    }

    /** @test */
    function it_gets_entries_from_all_collections_excluding_some_with_params()
    {
        $this->makePosts();

        $this->collectionTag->parameters = ['from' => '*', 'not_from' => 'art|music', 'title:contains' => 'love'];
        $this->assertCount(2, $this->collectionTag->index());

        $this->collectionTag->parameters = ['in' => '*', 'not_in' => 'art|music', 'title:contains' => 'love'];
        $this->assertCount(2, $this->collectionTag->index());

        $this->collectionTag->parameters = ['folder' => '*', 'not_folder' => 'art|music', 'title:contains' => 'love'];
        $this->assertCount(2, $this->collectionTag->index());

        $this->collectionTag->parameters = ['use' => '*', 'dont_use' => 'art|music', 'title:contains' => 'love'];
        $this->assertCount(2, $this->collectionTag->index());

        $this->collectionTag->parameters = ['collection' => '*', 'not_collection' => 'art|music', 'title:contains' => 'love'];
        $this->assertCount(2, $this->collectionTag->index());
    }
}
