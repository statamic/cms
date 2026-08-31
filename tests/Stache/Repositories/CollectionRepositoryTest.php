<?php

namespace Tests\Stache\Repositories;

use Illuminate\Support\Collection as IlluminateCollection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Stache\Repositories\CollectionRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\NavigationStore;
use Tests\TestCase;

class CollectionRepositoryTest extends TestCase
{
    private $directory;
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $this->app->instance(Stache::class, $stache);
        $this->directory = __DIR__.'/../__fixtures__/content/collections';
        $stache->registerStores([
            (new CollectionsStore($stache, app('files')))->directory($this->directory),
            (new EntriesStore($stache, app('files')))->directory($this->directory),
            (new NavigationStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/content/navigation'),
        ]);

        $this->repo = new CollectionRepository($stache);
    }

    #[Test]
    public function it_gets_all_collections()
    {
        $collections = $this->repo->all();

        $this->assertInstanceOf(IlluminateCollection::class, $collections);
        $this->assertCount(5, $collections);
        $this->assertEveryItemIsInstanceOf(Collection::class, $collections);

        $ordered = $collections->sortBy->handle()->values();
        $this->assertEquals(['alphabetical', 'blog', 'custom_class', 'numeric', 'pages'], $ordered->map->handle()->all());
        $this->assertEquals(['Alphabetical', 'Blog', 'Custom Class', 'Numeric', 'Pages'], $ordered->map->title()->all());
    }

    #[Test]
    public function it_gets_a_collection_by_handle()
    {
        tap($this->repo->findByHandle('alphabetical'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('alphabetical', $collection->handle());
            $this->assertEquals('Alphabetical', $collection->title());
        });

        tap($this->repo->findByHandle('blog'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('blog', $collection->handle());
            $this->assertEquals('Blog', $collection->title());
        });

        tap($this->repo->findByHandle('numeric'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('numeric', $collection->handle());
            $this->assertEquals('Numeric', $collection->title());
        });

        tap($this->repo->findByHandle('pages'), function ($collection) {
            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertEquals('pages', $collection->handle());
            $this->assertEquals('Pages', $collection->title());
        });

        $this->assertNull($this->repo->findByHandle('unknown'));
    }

    #[Test]
    public function it_saves_a_collection_to_the_stache_and_to_a_file()
    {
        $collection = CollectionAPI::make('new');
        $collection->cascade(['foo' => 'bar']);
        $this->assertNull($this->repo->findByHandle('new'));

        $this->repo->save($collection);

        $this->assertNotNull($item = $this->repo->findByHandle('new'));
        $this->assertEquals(['foo' => 'bar'], $item->cascade()->all());
        $this->assertTrue(file_exists($this->directory.'/new.yaml'));
        @unlink($this->directory.'/new.yaml');
    }

    #[Test]
    public function it_gets_additional_preview_targets()
    {
        $collection1 = (new Collection)->handle('test');
        $collection2 = (new Collection)->handle('test_2');

        $previewTargetsCollection1 = [
            ['label' => 'Foo', 'format' => '{foo}'],
        ];

        $previewTargetsCollection2 = [
            ['label' => 'Bar', 'format' => '{bar}'],
        ];

        CollectionAPI::addPreviewTargets('test', $previewTargetsCollection1);
        CollectionAPI::addPreviewTargets('test_2', $previewTargetsCollection2);

        $previewTargetsTest = CollectionAPI::additionalPreviewTargets('test');
        $previewTargetsTest2 = CollectionAPI::additionalPreviewTargets('test_2');

        $this->assertEquals($previewTargetsCollection1, $previewTargetsTest->all());
        $this->assertEquals($previewTargetsCollection2, $previewTargetsTest2->all());
        $this->assertNotEquals($previewTargetsTest->all(), $previewTargetsTest2->all());
    }

    #[Test]
    public function test_find_or_fail_gets_collection()
    {
        $collection = $this->repo->findOrFail('blog');

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals('Blog', $collection->title());
    }

    #[Test]
    public function test_find_or_fail_throws_exception_when_collection_does_not_exist()
    {
        $this->expectException(CollectionNotFoundException::class);
        $this->expectExceptionMessage('Collection [does-not-exist] not found');

        $this->repo->findOrFail('does-not-exist');
    }
}
