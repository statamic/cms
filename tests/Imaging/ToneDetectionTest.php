<?php

namespace Tests\Imaging;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Imaging\Attributes;
use Tests\TestCase;

class ToneDetectionTest extends TestCase
{
    private Attributes $attributes;

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.tone_fixtures' => [
            'driver' => 'local',
            'root' => __DIR__.'/../Fixtures/Imaging/tone',
        ]]);

        $this->attributes = app(Attributes::class);
    }

    #[Test]
    #[DataProvider('rasterToneProvider')]
    public function it_detects_tone_for_raster_images(string $filename, string $expectedTone): void
    {
        $disk = Storage::disk('tone_fixtures');
        $result = $this->attributes->from($disk, $filename);

        $this->assertSame($expectedTone, $result['tone'] ?? null, "Failed for {$filename}");
    }

    public static function rasterToneProvider(): array
    {
        return [
            'light jpg' => ['light.jpg', 'light'],
            'dark jpg' => ['dark.jpg', 'dark'],
            'light png' => ['light.png', 'light'],
            'dark png' => ['dark.png', 'dark'],
            'light transparent png' => ['light-transparent.png', 'light'],
            'dark transparent png' => ['dark-transparent.png', 'dark'],
        ];
    }

    #[Test]
    #[DataProvider('svgToneProvider')]
    public function it_detects_tone_for_svg(string $filename, string $expectedTone): void
    {
        if (! extension_loaded('imagick')) {
            throw new \RuntimeException('Imagick is not available. Add imagick to the PHP extensions in .github/workflows/tests.yml to run this test.');
        }

        $disk = Storage::disk('tone_fixtures');
        $result = $this->attributes->from($disk, $filename);

        $this->assertSame($expectedTone, $result['tone'] ?? null, "Failed for {$filename}");
    }

    public static function svgToneProvider(): array
    {
        return [
            'light svg' => ['light.svg', 'light'],
            'dark svg' => ['dark.svg', 'dark'],
        ];
    }

    #[Test]
    #[DataProvider('svgToneProvider')]
    public function it_detects_svg_tone_via_xml_fallback_when_imagick_unavailable(string $filename, string $expectedTone): void
    {
        \Facades\Statamic\Imaging\ImagickAvailability::shouldReceive('available')->andReturnFalse();

        $disk = Storage::disk('tone_fixtures');
        $result = $this->attributes->from($disk, $filename);

        $this->assertSame($expectedTone, $result['tone'] ?? null, "SVG XML fallback failed for {$filename}");
    }
}
