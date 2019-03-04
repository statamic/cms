<?php

namespace Tests\Stache\Stores;

use Mockery;
use Tests\TestCase;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Facades\Statamic\Stache\Traverser;
use Statamic\Stache\Stores\StructuresStore;
use Statamic\Contracts\Data\Structures\Structure;

class StructuresStoreTest extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        mkdir($this->tempDir = __DIR__.'/tmp');

        $stache = (new Stache)->sites(['en']);
        $this->store = (new StructuresStore($stache, app('files')))->directory($this->tempDir);
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
                                        'page' => 'pages-directors',
                                        'children' => [] // Empty array is here to test that it gets removed.
                                    ]
                                ]
                            ]
                        ],
                    ],
                    [
                        'page' => 'pages-blog',
                        'children' => [] // Empty array is here to test that it gets removed.
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

    /** @test */
    function it_adds_entry_uris_to_cacheable_meta()
    {
        $structure = Mockery::mock(\Statamic\Data\Structures\Structure::class);
        $structure->shouldReceive('handle')->andReturn('pages');
        $structure->shouldReceive('uris')->andReturn(collect([
            'pages-home' => '/',
            'pages-about' => '/about',
            'pages-board' => '/about/board',
            'pages-directors' => '/about/board/directors',
            'pages-blog' => '/blog',
        ]));

        $this->store->setItem('pages', $structure);

        $this->assertEquals([
            'paths' => ['en' => []],
            'uris' => ['en' => []],
            'entryUris' => [
                'en' => [
                    'pages::pages-home' => '/',
                    'pages::pages-about' => '/about',
                    'pages::pages-board' => '/about/board',
                    'pages::pages-directors' => '/about/board/directors',
                    'pages::pages-blog' => '/blog',
                ]
            ]
        ], $this->store->getCacheableMeta());
    }

    /** @test */
    function it_loads_entry_uris_from_meta()
    {
        $meta = [
            'paths' => [],
            'uris' => ['en' => []],
            'entryUris' => [
                'en' => [
                    'first::1' => '/',
                    'first::2' => '/foo',
                    'first::3' => '/foo/bar',
                    'second::4' => '/baz',
                    'second::5' => '/baz/qux',
                ],
            ]
        ];

        $this->store->loadMeta($meta);

        $expected = [
            'first::1' => '/',
            'first::2' => '/foo',
            'first::3' => '/foo/bar',
            'second::4' => '/baz',
            'second::5' => '/baz/qux',
        ];

        $this->assertEquals($expected, $this->store->getEntryUris()->all());
        $this->assertEquals($expected, $this->store->getEntryUris('en')->all());
    }

    /** @test */
    function when_a_structure_is_inserted_it_should_update_the_uris_and_remove_them_when_its_removed()
    {
        $structureOne = Mockery::mock(\Statamic\Data\Structures\Structure::class);
        $structureOne->shouldReceive('handle')->andReturn('one');
        $structureOne->shouldReceive('uris')->andReturn(collect([
            '1' => '/foo',
            '2' => '/foo/bar',
        ]));
        $structureTwo = Mockery::mock(\Statamic\Data\Structures\Structure::class);
        $structureTwo->shouldReceive('handle')->andReturn('two');
        $structureTwo->shouldReceive('uris')->andReturn(collect([
            '3' => '/baz',
            '4' => '/baz/qux',
        ]));

        $this->assertEquals([], $this->store->getEntryUris()->all());

        $this->store->setItem('one', $structureOne);
        $this->store->setItem('two', $structureTwo);

        $this->assertEquals([
            'one::1' => '/foo',
            'one::2' => '/foo/bar',
            'two::3' => '/baz',
            'two::4' => '/baz/qux',
        ], $this->store->getEntryUris()->all());

        $this->store->removeItem('two');

        $this->assertEquals([
            'one::1' => '/foo',
            'one::2' => '/foo/bar'
        ], $this->store->getEntryUris()->all());
    }

    /** @test */
    function removing_an_entry_from_a_structure_should_remove_its_uri()
    {
        $structure = Mockery::mock(\Statamic\Data\Structures\Structure::class);
        $structure->shouldReceive('handle')->andReturn('one');
        $structure->shouldReceive('uris')->andReturn(
            collect([ // first time
                '1' => '/foo',
                '2' => '/foo/bar',
            ]),
            collect([ // second time
                '1' => '/foo',
                '3' => '/qux',
            ])
        );

        $this->store->setItem('one', $structure);

        $this->assertEquals([
            'one::1' => '/foo',
            'one::2' => '/foo/bar',
        ], $this->store->getEntryUris()->all());

        $this->store->setItem('one', $structure);

        $this->assertEquals([
            'one::1' => '/foo',
            'one::3' => '/qux',
        ], $this->store->getEntryUris()->all());
    }

    /** @test */
    function it_gets_a_key_from_a_uri()
    {
        $this->store->setEntryUris([
            'en' => [
                'first::1' => '/',
                'first::2' => '/foo',
                'first::3' => '/foo/bar',
                'second::4' => '/baz',
                'second::5' => '/baz/qux',
            ],
        ]);

        $this->assertEquals('first::1', $this->store->getKeyFromUri('/'));
        $this->assertEquals('first::2', $this->store->getKeyFromUri('/foo'));
        $this->assertEquals('first::3', $this->store->getKeyFromUri('/foo/bar'));
        $this->assertEquals('second::4', $this->store->getKeyFromUri('/baz'));
        $this->assertEquals('second::5', $this->store->getKeyFromUri('/baz/qux'));
        $this->assertNull($this->store->getKeyFromUri('/unknown'));
    }
}
