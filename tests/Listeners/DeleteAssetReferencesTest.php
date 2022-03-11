<?php

namespace Tests\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades;
use Statamic\Support\Arr;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeleteAssetReferencesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'file']); // Doesn't work when they're arrays since the object is stored in memory.
        Cache::clear();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/tmp',
        ]]);

        Facades\Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            ],
        ]);

        $this->container = tap(Facades\AssetContainer::make()->handle('test_container')->disk('test'))->save();
        $this->asset1 = tap(Facades\Asset::make()->container('test_container')->path('asset1.jpg'))->save();
        $this->asset2 = tap(Facades\Asset::make()->container('test_container')->path('asset2.jpg'))->save();
        $this->asset3 = tap(Facades\Asset::make()->container('test_container')->path('asset3.jpg'))->save();

        Storage::fake('test');
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory(__DIR__.'/tmp');

        parent::tearDown();
    }

    /** @test */
    public function it_updates_single_assets_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'delete',
                    'field' => [
                        'type' => 'assets',
                        'container' => 'test_container',
                        'max_files' => 1,
                    ],
                ],
                [
                    'handle' => 'leave_be',
                    'field' => [
                        'type' => 'assets',
                        'container' => 'test_container',
                        'max_files' => 1,
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'delete' => 'asset1.jpg',
            'leave_be' => 'asset2.jpg',
        ]))->save();

        $this->assertEquals('asset1.jpg', $entry->get('delete'));
        $this->assertEquals('asset2.jpg', $entry->get('leave_be'));

        $this->asset1->delete();

        $this->assertNull($entry->fresh()->get('delete'));
        $this->assertEquals('asset2.jpg', $entry->fresh()->get('leave_be'));
    }

    /** @test */
    public function it_updates_multi_assets_fields()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'delete_one',
                    'field' => [
                        'type' => 'assets',
                        'container' => 'test_container',
                    ],
                ],
                [
                    'handle' => 'delete_two',
                    'field' => [
                        'type' => 'assets',
                        'container' => 'test_container',
                    ],
                ],
                [
                    'handle' => 'delete_all',
                    'field' => [
                        'type' => 'assets',
                        'container' => 'test_container',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'delete_one' => ['asset3.jpg', 'asset1.jpg'],
            'delete_two' => ['asset1.jpg', 'asset2.jpg', 'asset3.jpg'],
            'delete_all' => ['asset2.jpg', 'asset3.jpg'],
        ]))->save();

        $this->assertEquals(['asset3.jpg', 'asset1.jpg'], $entry->get('delete_one'));
        $this->assertEquals(['asset1.jpg', 'asset2.jpg', 'asset3.jpg'], $entry->get('delete_two'));

        $this->asset2->delete();
        $this->asset3->delete();

        $this->assertEquals(['asset1.jpg'], $entry->fresh()->get('delete_one'));
        $this->assertEquals(['asset1.jpg'], $entry->fresh()->get('delete_two'));
        $this->assertEquals([], $entry->fresh()->get('delete_all'));
    }

    /** @test */
    public function it_deletes_asset_references_in_bard_field()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'bardo',
                    'field' => [
                        'type' => 'bard',
                        'container' => 'test_container',
                    ],
                ],
            ],
        ]);

        $entry = tap(Facades\Entry::make()->collection($collection)->data([
            'bardo' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'asset::test_container::asset1.jpg',
                                'alt' => 'hoff',
                            ],
                        ],
                        [
                            'type' => 'link',
                            'attrs' => [
                                'href' => 'statamic://asset::test_container::asset1.jpg',
                            ],
                        ],
                        [
                            'type' => 'paragraph',
                            'content' => 'unrelated',
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'image',
                            'attrs' => [
                                'src' => 'asset::test_container::asset2.jpg',
                                'alt' => 'norris',
                            ],
                        ],
                        [
                            'type' => 'link',
                            'attrs' => [
                                'href' => 'statamic://asset::test_container::asset2.jpg',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => 'unrelated',
                ],
            ],
        ]))->save();

        $this->assertEquals('asset::test_container::asset1.jpg', Arr::get($entry->data(), 'bardo.0.content.0.attrs.src'));
        $this->assertEquals('hoff', Arr::get($entry->data(), 'bardo.0.content.0.attrs.alt'));
        $this->assertEquals('statamic://asset::test_container::asset1.jpg', Arr::get($entry->data(), 'bardo.0.content.1.attrs.href'));
        $this->assertEquals('asset::test_container::asset2.jpg', Arr::get($entry->data(), 'bardo.1.content.0.attrs.src'));
        $this->assertEquals('norris', Arr::get($entry->data(), 'bardo.1.content.0.attrs.alt'));
        $this->assertEquals('statamic://asset::test_container::asset2.jpg', Arr::get($entry->data(), 'bardo.1.content.1.attrs.href'));

        $this->asset1->delete();

        $this->assertNull(Arr::get($entry->fresh()->data(), 'bardo.0.content.0'));
        $this->assertNull(Arr::get($entry->fresh()->data(), 'bardo.0.content.1'));
        $this->assertEquals('asset::test_container::asset2.jpg', Arr::get($entry->fresh()->data(), 'bardo.1.content.0.attrs.src'));
        $this->assertEquals('norris', Arr::get($entry->fresh()->data(), 'bardo.1.content.0.attrs.alt'));
        $this->assertEquals('statamic://asset::test_container::asset2.jpg', Arr::get($entry->fresh()->data(), 'bardo.1.content.1.attrs.href'));
    }

    /** @test */
    public function it_updates_asset_references_in_bard_field_when_saved_as_html()
    {
        $collection = tap(Facades\Collection::make('articles'))->save();

        $this->setInBlueprints('collections/articles', [
            'fields' => [
                [
                    'handle' => 'bardo',
                    'field' => [
                        'type' => 'bard',
                        'container' => 'test_container',
                        'save_html' => true,
                    ],
                ],
            ],
        ]);

        $content = <<<'EOT'
<p>Some text.</p>
<img src="statamic://asset::test_container::asset1.jpg">
<img src="statamic://asset::test_container::asset1.jpg" alt="test">
</p>More text.</p>
<p><a href="statamic://asset::test_container::asset1.jpg">Link</a></p>
<img src="statamic://asset::test_container::asset2.jpg">
<p><a href="statamic://asset::test_container::asset2.jpg">Link</a></p>
EOT;

        $entry = tap(Facades\Entry::make()->collection($collection)->data(['bardo' => $content]))->save();

        $this->assertEquals($content, $entry->get('bardo'));

        $this->asset1->delete();

        $expected = <<<'EOT'
<p>Some text.</p>
<img src=>
<img src= alt="test">
</p>More text.</p>
<p><a href=>Link</a></p>
<img src="statamic://asset::test_container::asset2.jpg">
<p><a href="statamic://asset::test_container::asset2.jpg">Link</a></p>
EOT;

        $this->assertEquals($expected, $entry->fresh()->get('bardo'));
    }

    protected function setSingleBlueprint($namespace, $blueprintContents)
    {
        $blueprint = tap(Facades\Blueprint::make()->setContents($blueprintContents))->save();

        Facades\Blueprint::shouldReceive('find')->with($namespace)->andReturn($blueprint);
    }

    protected function setInBlueprints($namespace, $blueprintContents)
    {
        $blueprint = tap(Facades\Blueprint::make()->setContents($blueprintContents))->save();

        Facades\Blueprint::shouldReceive('in')->with($namespace)->andReturn(collect([$blueprint]));
    }
}
