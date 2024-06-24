<?php

namespace Tests\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Path;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\NavTreeStore;
use Statamic\Structures\NavTree;
use Tests\TestCase;

class NavTreeStoreTest extends TestCase
{
    private $tempDir;
    private $store;

    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new NavTreeStore($stache, app('files')))->directory($this->tempDir);

        Facades\Stache::registerStore($this->store);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem)->deleteDirectory($this->tempDir);
    }

    #[Test]
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

    #[Test]
    public function it_makes_nav_tree_instances_from_files()
    {
        $contents = <<<'YAML'
tree:
  -
    url: /foo
    title: Foo
  -
    url: /bar
    title: Bar
YAML;
        $item = $this->store->makeItemFromFile(Path::tidy($this->tempDir.'/links.yaml'), $contents);

        $this->assertInstanceOf(NavTree::class, $item);
        $this->assertEquals('en', $item->locale());
        $this->assertTree([
            ['url' => '/foo', 'title' => 'Foo'],
            ['url' => '/bar', 'title' => 'Bar'],
        ], $item);
    }

    #[Test]
    public function it_makes_nav_tree_instances_from_files_when_using_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/'],
            'fr' => ['url' => 'http://domain.com/fr/'],
        ]);

        $contents = <<<'YAML'
tree:
  -
    url: /le-foo
    title: Le Foo
  -
    url: /le-bar
    title: Le Bar
YAML;
        $item = $this->store->makeItemFromFile(Path::tidy($this->tempDir.'/fr/links.yaml'), $contents);

        $this->assertInstanceOf(NavTree::class, $item);
        $this->assertEquals('fr', $item->locale());
        $this->assertTree([
            ['url' => '/le-foo', 'title' => 'Le Foo'],
            ['url' => '/le-bar', 'title' => 'Le Bar'],
        ], $item);
    }

    #[Test]
    public function it_uses_the_handle_and_locale_as_the_item_key_for_nav_trees()
    {
        $this->assertEquals(
            'links::fr',
            $this->store->getItemKey(Facades\Nav::make('links')->makeTree('fr'))
        );
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $tree = Facades\Nav::make('links')->makeTree('en', [
            ['title' => 'Test', 'url' => '/test'],
        ]);

        $this->store->save($tree);

        $expected = <<<'EOT'
tree:
  -
    title: Test
    url: /test

EOT;

        $this->assertStringEqualsFile($this->tempDir.'/links.yaml', $expected);
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
