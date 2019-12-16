<?php

namespace Tests\Stache\Stores;

use Mockery;
use Statamic\Facades;
use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\StructuresStore;
use Statamic\Contracts\Structures\Structure;
use Statamic\Facades\Path;

class StructuresStoreTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new StructuresStore($stache, app('files')))->directory($this->tempDir);

        Facades\Stache::registerStore($this->store);
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
        $this->assertTrue(file_exists($dir.'/subdirectory/nested-one.yaml'));
        $this->assertTrue(file_exists($dir.'/subdirectory/nested-two.yaml'));
        $this->assertTrue(file_exists($dir.'/top-level-non-yaml-file.md'));
    }

    /** @test */
    function it_makes_structure_instances_from_files()
    {
        $contents = <<<'EOT'
title: Pages
route: '{parent_uri}/{slug}'
root: pages-home
tree:
  -
    page: pages-about
    children:
      -
        page: pages-board
        children:
          -
            page: pages-directors
  -
    page: pages-blog # (/blog)
EOT;
        $item = $this->store->makeItemFromFile(Path::tidy($this->tempDir.'/pages.yaml'), $contents);

        $this->assertInstanceOf(Structure::class, $item);
        $this->assertEquals('pages', $item->handle());
        $this->assertEquals('Pages', $item->title());
        // TODO: Some more assertions
    }

    /** @test */
    function it_uses_the_filename_as_the_item_key()
    {
        $this->assertEquals(
            'test',
            $this->store->getItemKey(Facades\Structure::make()->handle('test'))
        );
    }

    /** @test */
    function it_saves_to_disk()
    {
        $structure = Facades\Structure::make()->handle('pages');
        $structure->addTree($structure->makeTree('en'));

        $this->store->save($structure);

        $this->assertStringEqualsFile($this->tempDir.'/pages.yaml', $structure->fileContents());
    }

    /** @test */
    function it_saves_to_disk_with_multiple_sites()
    {
        $this->markTestIncomplete();
    }
}
