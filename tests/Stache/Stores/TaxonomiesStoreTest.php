<?php

namespace Tests\Stache\Stores;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\TaxonomiesStore;
use Statamic\Facades\Taxonomy as TaxonomyAPI;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Facades\Path;

class TaxonomiesStoreTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->app->instance(Stache::class, $stache);
        $stache->registerStore($this->store = (new TaxonomiesStore($stache, app('files')))->directory($this->tempDir));
    }

    function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    function it_only_gets_top_level_yaml_files()
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
        $this->assertTrue(file_exists($dir.'/top-level-non-yaml-file.md'));
    }

    /** @test */
    function it_makes_taxonomy_instances_from_files()
    {
        $item = $this->store->makeItemFromFile($this->tempDir.'/example.yaml', "title: Example\nfoo: bar");

        $this->assertInstanceOf(Taxonomy::class, $item);
        $this->assertEquals('example', $item->handle());
        $this->assertEquals('Example', $item->title());
    }

    /** @test */
    function it_uses_the_filename_as_the_item_key()
    {
        $this->assertEquals(
            'test',
            $this->store->getItemKey(TaxonomyAPI::make('test'))
        );
    }

    /** @test */
    function it_saves_to_disk()
    {
        $taxonomy = TaxonomyAPI::make('new');

        $this->store->save($taxonomy);

        $this->assertStringEqualsFile($this->tempDir.'/new.yaml', $taxonomy->fileContents());
    }
}
