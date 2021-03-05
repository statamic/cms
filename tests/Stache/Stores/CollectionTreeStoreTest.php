<?php

namespace Tests\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Filesystem\Filesystem;
use Statamic\Facades;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\CollectionTreeStore;
use Statamic\Structures\CollectionTree;
use Tests\TestCase;

class CollectionTreeStoreTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new CollectionTreeStore($stache, app('files')))->directory($this->tempDir);

        Facades\Stache::registerStore($this->store);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    /** @test */
    public function it_only_gets_yaml_files()
    {
        touch($this->tempDir.'/one.yaml', 1234567890);
        touch($this->tempDir.'/two.yaml', 1234567890);
        touch($this->tempDir.'/non-yaml-file.md', 1234567890);

        $files = Traverser::filter([$this->store, 'getItemFilter'])->traverse($this->store);

        $dir = Path::tidy($this->tempDir);
        $this->assertEquals([
            $dir.'/one.yaml' => 1234567890,
            $dir.'/two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($dir.'/non-yaml-file.md'));
    }

    /** @test */
    public function it_makes_collection_tree_instances_from_files()
    {
        $contents = <<<'YAML'
tree:
  -
    entry: 1
  -
    entry: 2
YAML;
        $item = $this->store->makeItemFromFile(Path::tidy($this->tempDir.'/pages.yaml'), $contents);

        $this->assertInstanceOf(CollectionTree::class, $item);
        $this->assertEquals('en', $item->locale());
        $this->assertTree([
            ['entry' => 1],
            ['entry' => 2],
        ], $item);
    }

    /** @test */
    public function it_makes_nav_tree_instances_from_files_when_using_multisite()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
        ]]);

        $contents = <<<'YAML'
tree:
  -
    entry: 3
  -
    entry: 4
YAML;
        $item = $this->store->makeItemFromFile(Path::tidy($this->tempDir.'/fr/pages.yaml'), $contents);

        $this->assertInstanceOf(CollectionTree::class, $item);
        $this->assertEquals('fr', $item->locale());
        $this->assertTree([
            ['entry' => 3],
            ['entry' => 4],
        ], $item);
    }

    /** @test */
    public function it_uses_the_handle_and_locale_as_the_item_key_for_nav_trees()
    {
        $collection = Facades\Collection::make('pages')->structureContents(['root' => true]);
        $tree = $collection->structure()->makeTree('fr');

        $this->assertEquals(
            'pages::fr',
            $this->store->getItemKey($tree)
        );
    }

    /** @test */
    public function it_saves_to_disk()
    {
        $collection = Facades\Collection::make('pages')->structureContents(['root' => true]);
        Facades\Collection::shouldReceive('findByHandle')->with('pages')->andReturn($collection);
        $tree = $collection->structure()->makeTree('en', [
            ['entry' => 'test'],
        ]);

        $this->store->save($tree);

        $expected = <<<'EOT'
tree:
  -
    entry: test

EOT;

        $this->assertStringEqualsFile($this->tempDir.'/pages.yaml', $expected);
    }

    private function assertTree($array, $item)
    {
        // Use reflection to check the tree array is correct.
        // When using tree() method, it will blink using the structure's handle
        // in the key. Within the store, we haven't yet associated it with
        // the structure. That'll happen later within the repository.
        $reflect = new \ReflectionObject($item);
        $property = $reflect->getProperty('tree');
        $property->setAccessible(true);
        $this->assertEquals($array, $property->getValue($item));
    }
}
