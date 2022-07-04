<?php

namespace Tests\Assets;

use Facades\Statamic\Assets\ExtractInfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Statamic\Assets\Asset;
use Statamic\Assets\Attributes;
use Statamic\Facades\AssetContainer;
use Tests\TestCase;

class AttributesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->attributes = app(Attributes::class);
    }

    /** @test */
    public function a_non_image_asset_has_no_attributes()
    {
        $asset = $this->mock(Asset::class);
        $asset->shouldReceive('isAudio')->andReturnFalse();
        $asset->shouldReceive('isImage')->andReturnFalse();
        $asset->shouldReceive('isSvg')->andReturnFalse();
        $asset->shouldReceive('isVideo')->andReturnFalse();

        $attributes = $this->attributes->asset($asset);

        $this->assertEquals([], $attributes->get());
    }

    /** @test */
    public function it_gets_the_attributes()
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
        [$width, $height] = getimagesize($realpath);
        $this->assertEquals(30, $width);
        $this->assertEquals(60, $height);

        $attributes = $this->attributes->asset($asset);

        $this->assertEquals(['width' => 30, 'height' => 60], $attributes->get());
    }

    /** @test */
    public function it_gets_the_attributes_of_audio_file()
    {
        $asset = (new Asset)
            ->container(AssetContainer::make('test-container')->disk('test'))
            ->path('path/to/asset.mp3');

        ExtractInfo::shouldReceive('fromAsset')->with($asset)->andReturn(['playtime_seconds' => 13]);

        $attributes = $this->attributes->asset($asset);

        $this->assertEquals(['duration' => 13], $attributes->get());
    }

    /** @test */
    public function it_gets_the_attributes_of_video_file()
    {
        $asset = (new Asset)
            ->container(AssetContainer::make('test-container')->disk('test'))
            ->path('path/to/asset.mp4');

        ExtractInfo::shouldReceive('fromAsset')->with($asset)->andReturn([
            'playtime_seconds' => 13,
            'video' => [
                'resolution_x' => 1920,
                'resolution_y' => 1080,
            ],
        ]);

        $attributes = $this->attributes->asset($asset);

        $this->assertEquals(['duration' => 13, 'width' => 1920, 'height' => 1080], $attributes->get());
    }

    /** @test */
    public function it_gets_the_attributes_of_an_svg()
    {
        $asset = $this->svgAsset('<svg width="30" height="60" viewBox="0 0 100 200"></svg>');

        $this->assertEquals(['width' => 30.0, 'height' => 60.0], $this->attributes->asset($asset)->get());
    }

    /** @test */
    public function it_uses_the_viewbox_if_the_svg_dimensions_havent_been_provided()
    {
        $asset = $this->svgAsset('<svg viewBox="0 0 300 600"></svg>');

        $this->assertEquals(['width' => 300, 'height' => 600], $this->attributes->asset($asset)->get());
    }

    /** @test */
    public function it_uses_the_viewbox_if_the_svg_dimensions_are_percents()
    {
        $asset = $this->svgAsset('<svg width="100%" height="100%" viewBox="0 0 300 600"></svg>');

        $this->assertEquals(['width' => 300, 'height' => 600], $this->attributes->asset($asset)->get());
    }

    /** @test */
    public function it_uses_the_viewbox_if_the_svg_dimensions_are_ems()
    {
        $asset = $this->svgAsset('<svg width="1em" height="2em" viewBox="0 0 300 600"></svg>');

        $this->assertEquals(['width' => 300, 'height' => 600], $this->attributes->asset($asset)->get());
    }

    /** @test */
    public function it_uses_default_attributes_if_the_svg_has_no_viewbox_and_is_missing_either_or_both_dimensions()
    {
        $this->assertEquals(['width' => 300, 'height' => 150], $this->attributes->asset($this->svgAsset('<svg></svg>'))->get());
        $this->assertEquals(['width' => 300, 'height' => 150], $this->attributes->asset($this->svgAsset('<svg width="100"></svg>'))->get());
        $this->assertEquals(['width' => 300, 'height' => 150], $this->attributes->asset($this->svgAsset('<svg height="100"></svg>'))->get());
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
