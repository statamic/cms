<?php

namespace Tests\Stache\Repositories;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Stache\Stores\StructuresStore;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Repositories\ContentRepository;

class ContentRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $stache->registerStores([
            (new CollectionsStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/collections'),
            (new EntriesStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/collections'),
            (new StructuresStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/structures'),
        ]);

        $this->repo = new ContentRepository($stache);
    }

    /** @test */
    function it_gets_all_content()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_gets_content_by_uri()
    {
        $entry = $this->repo->findByUri('/alphabetical/bravo');
        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertEquals('Bravo', $entry->get('title'));

        $entryInStructure = $this->repo->findByUri('/about/board/directors');
        $this->assertInstanceOf(Entry::class, $entryInStructure);
        $this->assertEquals('Directors', $entryInStructure->get('title'));

        $this->assertNull($this->repo->findByUri('/unknown'));
    }
}
