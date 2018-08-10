<?php

namespace Tests\Stache\Stores;

use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\StructuresStore;
use Statamic\Contracts\Data\Structures\Structure;

class StructuresStoreTest extends TestCase
{
    function setUp()
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new StructuresStore($stache, app('files')))->directory($this->tempDir);
    }

    function tearDown()
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

        $files = Traverser::traverse($this->store);

        $this->assertEquals([
            $this->tempDir.'/one.yaml' => 1234567890,
            $this->tempDir.'/two.yaml' => 1234567890,
        ], $files->all());

        // Sanity check. Make sure the file is there but wasn't included.
        $this->assertTrue(file_exists($this->tempDir.'/top-level-non-yaml-file.md'));
    }

    /** @test */
    function it_makes_structure_instances_from_cache()
    {
        $structure = [
            'title' => 'Pages',
            'route' => '{parent_uri}/{slug}',
            'root' => 'pages-home',
            'tree' => [
                [
                    'page' => 'pages-about',
                    'children' => [
                        [
                            'page' => 'pages-board',
                            'children' => [
                                [
                                    'page' => 'pages-directors'
                                ]
                            ]
                        ]
                    ],
                ],
                [
                    'page' => 'pages-blog'
                ],
            ]
        ];

        $items = $this->store->getItemsFromCache(collect(['123' => $structure]));

        $this->assertCount(1, $items);
        $item = $items->first();
        $this->assertInstanceOf(Structure::class, $item);
        // TODO: Some more assertions
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
        $item = $this->store->createItemFromFile($this->tempDir.'/pages.yaml', $contents);

        $this->assertInstanceOf(Structure::class, $item);
        $this->assertEquals('pages', $item->handle());
        $this->assertEquals([
            'title' => 'Pages',
            'route' => '{parent_uri}/{slug}',
            'root' => 'pages-home',
            'tree' => [
                [
                    'page' => 'pages-about',
                    'children' => [
                        [
                            'page' => 'pages-board',
                            'children' => [
                                [
                                    'page' => 'pages-directors'
                                ]
                            ]
                        ]
                    ],
                ],
                [
                    'page' => 'pages-blog'
                ],
            ]
        ], $item->data());
        // TODO: Some more assertions
    }

    /** @test */
    function it_uses_the_filename_as_the_item_key()
    {
        $this->assertEquals(
            'test',
            $this->store->getItemKey('irrelevant', '/path/to/test.yaml')
        );
    }

    /** @test */
    function it_saves_to_disk()
    {
        $structure = (new \Statamic\Data\Structures\Structure)
            ->handle('pages')
            ->data([
                'title' => 'Pages',
                'route' => '{parent_uri}/{slug}',
                'root' => 'pages-home',
                'tree' => [
                    [
                        'page' => 'pages-about',
                        'children' => [
                            [
                                'page' => 'pages-board',
                                'children' => [
                                    [
                                        'page' => 'pages-directors'
                                    ]
                                ]
                            ]
                        ],
                    ],
                    [
                        'page' => 'pages-blog'
                    ],
                ]
            ]);

        $this->store->save($structure);

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
    page: pages-blog

EOT;

        $this->assertStringEqualsFile($this->tempDir.'/pages.yaml', $contents);
    }
}
