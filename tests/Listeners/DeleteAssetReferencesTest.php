<?php

namespace Tests\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades;
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
