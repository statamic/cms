<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\AssetContainer as AssetContainerFacade;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetsMetaTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /**
     * @var AssetContainer
     */
    private $container;

    /**
     * @var array
     */
    private $sampleTextFileContentArray = [
        'data' => [
            'foo' => 'bar',
        ],
        'size' => 6,
        'last_modified' => 1665086377,
        'width' => null,
        'height' => null,
        'mime_type' => 'text/plain',
        'duration' => null,
    ];

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');
    }

    private function containerWithDisk()
    {
        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
        ]]);

        $container = (new AssetContainer)->handle('test')->disk('test');

        AssetContainerFacade::partialMock()
            ->shouldReceive('findByHandle')
            ->andReturn($container);

        return $container;
    }

    #[Test]
    public function it_generates_one_asset_meta_file_for_asset_with_no_meta_file()
    {
        $this->containerWithDisk();

        Storage::disk('test')->assertMissing('foo/bar.txt');
        Storage::disk('test')->assertMissing('foo/.meta/bar.txt.yaml');

        Storage::disk('test')->put('foo/bar.txt', 'foobar');

        Storage::disk('test')->assertExists('foo/bar.txt');

        Storage::disk('test')->assertMissing('foo/.meta/bar.txt.yaml');

        $this->artisan('statamic:assets:meta test_container')
            ->expectsOutputToContain('Generated metadata for 1 asset.');

        Storage::disk('test')->assertExists('foo/bar.txt');
        Storage::disk('test')->assertExists('foo/.meta/bar.txt.yaml');
    }

    #[Test]
    public function it_preserves_data_property_in_meta_data_file()
    {
        $this->containerWithDisk();

        Storage::disk('test')->put('foo/bar.txt', 'foobar');
        Storage::disk('test')->put(
            'foo/.meta/bar.txt.yaml',
            YAML::dump($this->sampleTextFileContentArray)
        );

        $this->artisan('statamic:assets:meta test_container')
            ->expectsOutputToContain('Generated metadata for 1 asset.');

        $this->assertEquals(
            Arr::get(YAML::parse(Storage::disk('test')->get('foo/.meta/bar.txt.yaml')), 'data.foo'),
            'bar'
        );
    }
}
