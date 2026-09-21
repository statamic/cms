<?php

namespace Tests\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Facades\Path;
use Statamic\Facades\Taxonomy as TaxonomyAPI;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\TaxonomiesStore;
use Tests\TestCase;

class TaxonomiesStoreTest extends TestCase
{
    private $tempDir;
    private $store;

    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->app->instance(Stache::class, $stache);
        $stache->registerStore($this->store = (new TaxonomiesStore($stache, app('files')))->directory($this->tempDir));
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
        $this->assertTrue(file_exists($dir.'/top-level-non-yaml-file.md'));
    }

    #[Test]
    public function it_makes_taxonomy_instances_from_files()
    {
        $item = $this->store->makeItemFromFile($this->tempDir.'/example.yaml', "title: Example\nfoo: bar");

        $this->assertInstanceOf(Taxonomy::class, $item);
        $this->assertEquals('example', $item->handle());
        $this->assertEquals('Example', $item->title());
    }

    #[Test]
    public function it_hydrates_sort_field_and_direction()
    {
        $contents = <<<'YAML'
title: Tags
sort_by: foo
sort_dir: desc
YAML;

        $item = $this->store->makeItemFromFile($this->tempDir.'/tags.yaml', $contents);

        $this->assertEquals('foo', $item->sortField());
        $this->assertEquals('desc', $item->sortDirection());
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
    public function it_does_not_hydrate_a_structure_when_the_key_is_absent()
    {
        $item = $this->store->makeItemFromFile($this->tempDir.'/example.yaml', 'title: Example');

        $this->assertFalse($item->hasStructure());
    }

    #[Test]
    public function it_does_not_hydrate_a_structure_when_the_value_is_false()
    {
        $item = $this->store->makeItemFromFile($this->tempDir.'/example.yaml', "title: Example\nstructure: false");

        $this->assertFalse($item->hasStructure());
    }

    #[Test]
    public function it_does_not_hydrate_a_structure_when_the_value_is_true()
    {
        $item = $this->store->makeItemFromFile($this->tempDir.'/example.yaml', "title: Example\nstructure: true");

        $this->assertFalse($item->hasStructure());
    }

    #[Test]
    public function it_hydrates_an_unbounded_structure_when_the_value_is_an_empty_array()
    {
        $item = $this->store->makeItemFromFile($this->tempDir.'/example.yaml', "title: Example\nstructure: {}");

        $this->assertTrue($item->hasStructure());
        $this->assertNull($item->structure()->maxDepth());
    }

    #[Test]
    public function it_hydrates_a_structure_with_a_max_depth()
    {
        $contents = <<<'YAML'
title: Example
structure:
  max_depth: 3
YAML;

        $item = $this->store->makeItemFromFile($this->tempDir.'/example.yaml', $contents);

        $this->assertTrue($item->hasStructure());
        $this->assertEquals(3, $item->structure()->maxDepth());
    }

    #[Test]
    public function it_uses_the_filename_as_the_item_key()
    {
        $this->assertEquals(
            'test',
            $this->store->getItemKey(TaxonomyAPI::make('test'))
        );
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $taxonomy = TaxonomyAPI::make('new');

        $this->store->save($taxonomy);

        $this->assertStringEqualsFile($this->tempDir.'/new.yaml', $taxonomy->fileContents());
    }
}
