<?php

namespace Tests\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Collection;
use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Facades\Path;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Stores\EntriesStore;
use Tests\TestCase;

class CollectionsStoreTest extends TestCase
{
    private $tempDir;
    private $store;

    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->app->instance(Stache::class, $stache);
        $stache->registerStore((new EntriesStore)->directory($this->tempDir));
        $stache->registerStore($this->store = (new CollectionsStore($stache, app('files')))->directory($this->tempDir));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    #[Test]
    public function it_only_gets_top_level_yaml_files()
    {
        touch($this->tempDir.'/one.yaml', 1234567890);
        touch($this->tempDir.'/two.yaml', 1234567890);
        touch($this->tempDir.'/three.txt', 1234567890);
        mkdir($this->tempDir.'/subdirectory');
        touch($this->tempDir.'/subdirectory/nested-one.yaml', 1234567890);
        touch($this->tempDir.'/subdirectory/nested-two.yaml', 1234567890);
        touch($this->tempDir.'/top-level-non-yaml-file.md', 1234567890);

        $files = Traverser::filter([$this->store, 'getItemFilter'])->traverse($this->store);

        $dir = Path::tidy($this->tempDir);
        $this->assertEquals([
            $dir.'/one.yaml' => 1234567890,
            $dir.'/two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($this->tempDir.'/top-level-non-yaml-file.md'));
    }

    #[Test]
    public function it_makes_collection_instances_from_files()
    {
        $item = $this->store->makeItemFromFile($this->tempDir.'/example.yaml', 'title: Example');

        $this->assertInstanceOf(Collection::class, $item);
        $this->assertEquals('example', $item->handle());
        $this->assertEquals('Example', $item->title());
    }

    #[Test]
    public function it_normalizes_preview_target_url_into_format()
    {
        // it's just nicer to write "url" into yaml than "format".

        $contents = <<<'YAML'
preview_targets:
  - { label: Foo, url: '/{bar}', refresh: true }
  - { label: Baz, url: '/{qux}', refresh: false }
  - { label: Quux, url: '/{flux}' }
YAML;

        $item = $this->store->makeItemFromFile($this->tempDir.'/example.yaml', $contents);

        $this->assertEquals([
            ['label' => 'Foo', 'format' => '/{bar}', 'refresh' => true],
            ['label' => 'Baz', 'format' => '/{qux}', 'refresh' => false],
            ['label' => 'Quux', 'format' => '/{flux}', 'refresh' => true],
        ], $item->previewTargets()->all());
    }

    #[Test]
    public function it_uses_the_filename_as_the_item_key()
    {
        $this->assertEquals(
            'test',
            $this->store->getItemKey(CollectionAPI::make('test'))
        );
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $collection = CollectionAPI::make('new');

        $this->store->save($collection);

        $this->assertStringEqualsFile($this->tempDir.'/new.yaml', $collection->fileContents());
    }
}
