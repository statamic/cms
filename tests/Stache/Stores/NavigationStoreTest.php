<?php

namespace Tests\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Structures\Nav;
use Statamic\Facades;
use Statamic\Facades\Path;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\NavigationStore;
use Tests\TestCase;

class NavigationStoreTest extends TestCase
{
    private $tempDir;
    private $store;

    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new NavigationStore($stache, app('files')))->directory($this->tempDir);

        Facades\Stache::registerStore($this->store);
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
        $this->assertTrue(file_exists($dir.'/subdirectory/nested-one.yaml'));
        $this->assertTrue(file_exists($dir.'/subdirectory/nested-two.yaml'));
        $this->assertTrue(file_exists($dir.'/top-level-non-yaml-file.md'));
    }

    #[Test]
    public function it_makes_structure_instances_from_files()
    {
        $contents = <<<'EOT'
title: Pages
route: '{parent_uri}/{slug}'
tree:
  -
    entry: pages-home
  -
    entry: pages-about
    children:
      -
        entry: pages-board
        children:
          -
            entry: pages-directors
  -
    entry: pages-blog # (/blog)
EOT;
        $item = $this->store->makeItemFromFile(Path::tidy($this->tempDir.'/pages.yaml'), $contents);

        $this->assertInstanceOf(Nav::class, $item);
        $this->assertEquals('pages', $item->handle());
        $this->assertEquals('Pages', $item->title());
        // TODO: Some more assertions
    }

    #[Test]
    public function it_uses_the_filename_as_the_item_key()
    {
        $this->assertEquals(
            'test',
            $this->store->getItemKey(Facades\Nav::make()->handle('test'))
        );
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $structure = Facades\Nav::make()->handle('pages');

        $this->store->save($structure);

        $this->assertStringEqualsFile($this->tempDir.'/pages.yaml', $structure->fileContents());
    }

    #[Test]
    public function it_saves_to_disk_with_multiple_sites()
    {
        $this->markTestIncomplete();
    }
}
