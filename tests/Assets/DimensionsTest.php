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
    private $dimensions;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->dimensions = new Dimensions(app(ImageGenerator::class));
    }

    /** @test */
    public function a_non_image_asset_has_no_dimensions()
    {
        $asset = $this->mock(Asset::class);
        $asset->shouldReceive('isImage')->andReturnFalse();
        $asset->shouldReceive('isSvg')->andReturnFalse();
        $asset->shouldReceive('isAudio')->andReturnFalse();
        $asset->shouldReceive('isVideo')->andReturnFalse();

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
        $realpath = Storage::disk('test')->path('path/to/asset.jpg');
        $this->assertFileExists($realpath);
        $imagesize = getimagesize($realpath);
        $this->assertEquals([30, 60], array_splice($imagesize, 0, 2));

        $dimensions = $this->dimensions->asset($asset);

        $this->assertEquals([30, 60], $dimensions->get());
        $this->assertEquals(30, $dimensions->width());
        $this->assertEquals(60, $dimensions->height());
    }

    /** @test */
    public function it_gets_the_dimensions_of_an_svg()
    {
        $asset = $this->svgAsset('<svg width="30" height="60" viewBox="0 0 100 200"></svg>');

        $this->assertEquals([30, 60], $this->dimensions->asset($asset)->get());
    }

    /** @test */
    public function it_uses_the_viewbox_if_the_svg_dimensions_havent_been_provided()
    {
        $asset = $this->svgAsset('<svg viewBox="0 0 300 600"></svg>');

        $this->assertEquals([300, 600], $this->dimensions->asset($asset)->get());
    }

    /** @test */
    public function it_uses_the_viewbox_if_the_svg_dimensions_are_percents()
    {
        $asset = $this->svgAsset('<svg width="100%" height="100%" viewBox="0 0 300 600"></svg>');

        $this->assertEquals([300, 600], $this->dimensions->asset($asset)->get());
    }

    /** @test */
    public function it_uses_the_viewbox_if_the_svg_dimensions_are_ems()
    {
        $asset = $this->svgAsset('<svg width="1em" height="2em" viewBox="0 0 300 600"></svg>');

        $this->assertEquals([300, 600], $this->dimensions->asset($asset)->get());
    }

    /** @test */
    public function it_uses_default_dimensions_if_the_svg_has_no_viewbox_and_is_missing_either_or_both_dimensions()
    {
        $this->assertEquals([300, 150], $this->dimensions->asset($this->svgAsset('<svg></svg>'))->get());
        $this->assertEquals([300, 150], $this->dimensions->asset($this->svgAsset('<svg width="100"></svg>'))->get());
        $this->assertEquals([300, 150], $this->dimensions->asset($this->svgAsset('<svg height="100"></svg>'))->get());
    }

    private function svgAsset($svg)
    {
        $asset = (new Asset)
            ->container(AssetContainer::make('test-container')->disk('test'))
            ->path('path/to/asset.svg');

        Storage::disk('test')->put('path/to/asset.svg', $svg);

        return $asset;
    }
}
