<?php

namespace Tests\Assets;

use PHPUnit\Framework\Attributes\DataProvider;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Assets\Thumbnails\ThumbnailService;
use Tests\Fixtures\Addon\ThumbnailGenerators;
use Tests\TestCase;

class ThumbnailServiceTest extends TestCase
{
    /**
     * @var ThumbnailService
     */
    protected $service;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.public' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
            'url' => '/the-url',
        ]]);

        config(['filesystems.disks.private' => [
            'driver' => 'local',
            'root' => __DIR__.'/__fixtures__/container',
            'url' => null,
        ]]);

        // Register custom thumbnail generators from data provider
        $arguments = $this->providedData();
        if (is_array($arguments[0] ?? null)) {
            config(['statamic.cp.thumbnail_generators' => $arguments[0]]);
        }
    }

    #[DataProvider('thumbnailAssetProvider')]
    public function testThumbnailUrls($generators, $asset, $expected)
    {
        $this->assertEquals(
            $expected,
            ThumbnailService::generate($asset),
            "Wrong thumbnail URL for asset {$asset->basename()}"
        );
    }

    public function testInterface()
    {
        config(['statamic.cp.thumbnail_generators' => [
            ThumbnailGenerators\MissingInterface::class,
        ]]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Thumbnail generator must implement [Statamic\Contracts\Assets\ThumbnailGenerator]!');

        $asset = static::createAsset('foo.txt');
        ThumbnailService::generator($asset);
    }

    public static function thumbnailAssetProvider()
    {
        $txt = static::createAsset('foo.txt');
        $jpg = static::createAsset('foo.jpg');
        $png = static::createAsset('foo.png');
        $svg = static::createAsset('foo.svg');
        $privateSvg = static::createAsset('foo.svg', 'private');
        $pdf = static::createAsset('foo.pdf');
        $video = static::createAsset('foo.mp4');

        return [
            [[], $txt, null],
            [[], $jpg, 'http://localhost/cp/thumbnails/'.base64_encode($jpg->id())],
            [[], $png, 'http://localhost/cp/thumbnails/'.base64_encode($png->id())],
            [[], $svg, '/the-url/img/foo.svg'],
            [[], $privateSvg, 'http://localhost/cp/svgs/'.base64_encode($privateSvg->id())],
            [[], $pdf, null],
            [[], $video, null],

            [[ThumbnailGenerators\Random::class], $video, 'https://picsum.photos/200/300'],
            [[ThumbnailGenerators\Random::class], $jpg, 'https://picsum.photos/200/300'],

            [[ThumbnailGenerators\Videos::class], $video, '/custom/video/thumb/'.base64_encode($video->id())],
            [[ThumbnailGenerators\Videos::class], $jpg, 'http://localhost/cp/thumbnails/'.base64_encode($jpg->id())],
        ];
    }

    protected static function createAsset(string $filename, string $disk = 'public')
    {
        $container = (new AssetContainer)->handle('main')->disk($disk);
        $asset = new Asset;
        $asset->container($container);
        $asset->path("img/{$filename}");

        return $asset;
    }
}
