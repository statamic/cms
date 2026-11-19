<?php

namespace Tests\Assets;

use Facades\Statamic\Assets\ExtractInfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;
use Statamic\Assets\Attributes;
use Statamic\Facades\AssetContainer;
use Tests\TestCase;

class AttributesTest extends TestCase
{
    private $attributes;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test');

        $this->attributes = app(Attributes::class);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_gets_the_attributes_of_audio_file()
    {
        $asset = (new Asset)
            ->container(AssetContainer::make('test-container')->disk('test'))
            ->path('path/to/asset.mp3');

        ExtractInfo::shouldReceive('fromAsset')->with($asset)->andReturn(['playtime_seconds' => 13]);

        $attributes = $this->attributes->asset($asset);

        $this->assertEquals(['duration' => 13], $attributes->get());
    }

    #[Test]
    #[DataProvider('videoProvider')]
    public function it_gets_the_attributes_of_video_file($playtimeSeconds, $resolutionX, $resolutionY, $rotate, $expected)
    {
        $asset = (new Asset)
            ->container(AssetContainer::make('test-container')->disk('test'))
            ->path('path/to/asset.mp4');

        ExtractInfo::shouldReceive('fromAsset')->with($asset)->andReturn([
            'playtime_seconds' => $playtimeSeconds,
            'video' => [
                'resolution_x' => $resolutionX,
                'resolution_y' => $resolutionY,
                'rotate' => $rotate,
            ],
        ]);

        $attributes = $this->attributes->asset($asset);

        $this->assertEquals($expected, $attributes->get());
    }

    public static function videoProvider()
    {
        return [
            'not rotated' => [13, 1920, 1080, null, ['duration' => 13, 'width' => 1920, 'height' => 1080]],
            'rotated 90' => [13, 1920, 1080, 90, ['duration' => 13, 'width' => 1080, 'height' => 1920]],
            'rotated -90' => [13, 1920, 1080, -90, ['duration' => 13, 'width' => 1080, 'height' => 1920]],
            'rotated 270' => [13, 1920, 1080, 270, ['duration' => 13, 'width' => 1080, 'height' => 1920]],
            'rotated -270' => [13, 1920, 1080, -270, ['duration' => 13, 'width' => 1080, 'height' => 1920]],
            'rotated 180' => [13, 1920, 1080, 180, ['duration' => 13, 'width' => 1920, 'height' => 1080]],
        ];
    }

    #[Test]
    public function it_gets_the_attributes_of_an_svg()
    {
        $asset = $this->svgAsset('<svg width="30" height="60" viewBox="0 0 100 200"></svg>');

        $this->assertEquals(['width' => 30.0, 'height' => 60.0], $this->attributes->asset($asset)->get());
    }

    #[Test]
    public function it_uses_the_viewbox_if_the_svg_dimensions_havent_been_provided()
    {
        $asset = $this->svgAsset('<svg viewBox="0 0 300 600"></svg>');

        $this->assertEquals(['width' => 300, 'height' => 600], $this->attributes->asset($asset)->get());
    }

    #[Test]
    public function it_uses_the_viewbox_if_the_svg_dimensions_are_percents()
    {
        $asset = $this->svgAsset('<svg width="100%" height="100%" viewBox="0 0 300 600"></svg>');

        $this->assertEquals(['width' => 300, 'height' => 600], $this->attributes->asset($asset)->get());
    }

    #[Test]
    public function it_uses_the_viewbox_if_the_svg_dimensions_are_ems()
    {
        $asset = $this->svgAsset('<svg width="1em" height="2em" viewBox="0 0 300 600"></svg>');

        $this->assertEquals(['width' => 300, 'height' => 600], $this->attributes->asset($asset)->get());
    }

    #[Test]
    public function it_uses_default_attributes_if_the_svg_has_no_viewbox_and_is_missing_either_or_both_dimensions()
    {
        $this->assertEquals(['width' => 300, 'height' => 150], $this->attributes->asset($this->svgAsset('<svg></svg>'))->get());
        $this->assertEquals(['width' => 300, 'height' => 150], $this->attributes->asset($this->svgAsset('<svg width="100"></svg>'))->get());
        $this->assertEquals(['width' => 300, 'height' => 150], $this->attributes->asset($this->svgAsset('<svg height="100"></svg>'))->get());
    }

    #[Test]
    #[DataProvider('exifOrientationProvider')]
    public function it_respects_exif_orientation_when_getting_image_dimensions($orientation, $expectedWidth, $expectedHeight)
    {
        $asset = (new Asset)
            ->container(AssetContainer::make('test-container')->disk('test'))
            ->path('path/to/asset.jpg');

        $jpeg = $this->createJpegWithOrientation(4032, 3024, $orientation);
        Storage::disk('test')->put('path/to/asset.jpg', $jpeg);

        $attributes = $this->attributes->asset($asset)->get();

        $this->assertEquals($expectedWidth, $attributes['width']);
        $this->assertEquals($expectedHeight, $attributes['height']);
    }

    public static function exifOrientationProvider()
    {
        return [
            'orientation 1 (normal)' => [1, 4032, 3024],
            'orientation 2 (flip horizontal)' => [2, 4032, 3024],
            'orientation 3 (rotate 180)' => [3, 4032, 3024],
            'orientation 4 (flip vertical)' => [4, 4032, 3024],
            'orientation 5 (transpose)' => [5, 3024, 4032],
            'orientation 6 (rotate 90 CW)' => [6, 3024, 4032],
            'orientation 7 (transverse)' => [7, 3024, 4032],
            'orientation 8 (rotate 90 CCW)' => [8, 3024, 4032],
        ];
    }

    private function createJpegWithOrientation(int $width, int $height, int $orientation): string
    {
        $img = imagecreatetruecolor($width, $height);
        ob_start();
        imagejpeg($img);
        $jpeg = ob_get_clean();
        imagedestroy($img);

        $tiff = pack('A2', 'II')
            .pack('v', 42)
            .pack('V', 8)
            .pack('v', 1)
            .pack('v', 0x0112)
            .pack('v', 3)
            .pack('V', 1)
            .pack('v', $orientation)."\x00\x00"
            .pack('V', 0);

        $app1 = "Exif\x00\x00".$tiff;
        $app1Segment = "\xFF\xE1".pack('n', strlen($app1) + 2).$app1;

        return substr($jpeg, 0, 2).$app1Segment.substr($jpeg, 2);
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
