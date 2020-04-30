<?php

namespace Tests\Assets;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Assets\Dimensions;
use Statamic\Facades\AssetContainer;
use Statamic\Imaging\ImageGenerator;
use Tests\TestCase;

class DimensionsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__.'/doesnt-matter-itll-get-faked-anyway',
        ]]);

        Storage::fake('test');

        $this->dimensions = new Dimensions(app(ImageGenerator::class));
    }

    /** @test */
    public function a_non_image_asset_has_no_dimensions()
    {
        $asset = $this->mock(Asset::class);
        $asset->shouldReceive('isImage')->andReturnFalse();

        $dimensions = $this->dimensions->asset($asset);

        $this->assertEquals([null, null], $dimensions->get());
        $this->assertEquals(null, $dimensions->width());
        $this->assertEquals(null, $dimensions->height());
    }

    /** @test */
    public function it_gets_the_dimensions()
    {
        Carbon::setTestNow(now());

        $asset = (new Asset)
            ->container(AssetContainer::make('test-container')->disk('test'))
            ->path('path/to/asset.jpg');

        $file = UploadedFile::fake()->image('asset.jpg', 30, 60);
        Storage::disk('test')->putFileAs('path/to', $file, 'asset.jpg');

        // Test about the actual file, for good measure.
        $realpath = Storage::disk('test')->getAdapter()->getPathPrefix().'path/to/asset.jpg';
        $this->assertFileExists($realpath);
        $imagesize = getimagesize($realpath);
        $this->assertEquals([30, 60], array_splice($imagesize, 0, 2));

        $dimensions = $this->dimensions->asset($asset);

        $this->assertEquals([30, 60], $dimensions->get());
        $this->assertEquals(30, $dimensions->width());
        $this->assertEquals(60, $dimensions->height());
    }
}
