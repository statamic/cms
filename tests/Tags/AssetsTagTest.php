<?php

namespace Tests\Tags;

use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetCollection;
use Statamic\Assets\AssetContainer;
use Statamic\Facades;
use Statamic\Facades\Parse;
use Tests\TestCase;

class AssetsTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/tmp',
        ]]);

        Storage::fake('test');
        Storage::fake('dimensions-cache');
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }

    /** @test */
    public function it_outputs_assets()
    {
        $container = (new AssetContainer)
            ->handle('test_container')
            ->disk('test');

        $assets = collect([1, 2, 3])
            ->map(function ($item, $key) use ($container) {
                return (new Asset)
                    ->container($container)
                    ->set('title', $item);
            })->values();

        $ac = AssetCollection::make($assets);

        Facades\AssetContainer::shouldReceive('findByHandle')
            ->with('test_container')
            ->andReturn($container);

        Facades\AssetContainer::shouldReceive('find')
            ->with('test_container')
            ->andReturn($container);

        $container = $this->mock(AssetContainer::class);
        $container
            ->shouldReceive('assets')
            ->with([null, false])
            ->andReturn($ac);

        $this->assertEquals(
            '123',
            $this->tag('{{ assets container="test_container" }}{{ title }}{{ /assets }}')
        );
    }
}
