<?php

namespace Tests\Assets;

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

        $this->service = new ThumbnailService();
    }

    public function testJpeg()
    {
        $asset = $this->createAsset('foo.jpg');

        $encoded = base64_encode($asset->id());

        $this->assertEquals(
            ThumbnailService::generate($asset),
            "http://localhost/cp/thumbnails/$encoded"
        );
    }

    public function testPng()
    {
        $asset = $this->createAsset('foo.png');

        $encoded = base64_encode($asset->id());

        $this->assertEquals(
            ThumbnailService::generate($asset),
            "http://localhost/cp/thumbnails/$encoded"
        );
    }

    public function testPublicSvg()
    {
        $asset = $this->createAsset('foo.svg');

        $this->assertEquals(
            ThumbnailService::generate($asset),
            '/the-url/img/foo.svg'
        );
    }

    public function testPrivateSvg()
    {
        $asset = $this->createAsset('foo.svg', 'private');

        $encoded = base64_encode($asset->id());

        $this->assertEquals(
            ThumbnailService::generate($asset),
            "http://localhost/cp/svgs/$encoded"
        );
    }

    public function testTxt()
    {
        $asset = $this->createAsset('foo.txt');

        $this->assertEquals(
            ThumbnailService::generate($asset),
            null
        );
    }

    public function testPdf()
    {
        $asset = $this->createAsset('foo.pdf');

        $this->assertEquals(
            ThumbnailService::generate($asset),
            null
        );
    }

    public function testWithoutCustomGenerator()
    {
        $text = $this->createAsset('foo.txt');
        $video = $this->createAsset('foo.mp4');
        $image = $this->createAsset('foo.jpg');

        $this->assertEquals(
            ThumbnailService::generate($text),
            null
        );

        $this->assertEquals(
            ThumbnailService::generate($video),
            null
        );

        $encoded = base64_encode($image->id());

        $this->assertEquals(
            ThumbnailService::generate($image),
            "http://localhost/cp/thumbnails/$encoded"
        );
    }

    public function testCustomVideoGenerator()
    {
        ThumbnailGenerators\Videos::register();

        $text = $this->createAsset('foo.txt');
        $video = $this->createAsset('foo.mp4');
        $image = $this->createAsset('foo.jpg');

        $this->assertEquals(
            ThumbnailService::generate($text),
            null
        );

        $encoded = base64_encode($video->id());

        $this->assertEquals(
            ThumbnailService::generate($video),
            "/custom/video/thumb/$encoded"
        );

        $encoded = base64_encode($image->id());

        $this->assertEquals(
            ThumbnailService::generate($image),
            "http://localhost/cp/thumbnails/$encoded"
        );
    }

    public function testCustomRandomGenerator()
    {

        ThumbnailGenerators\Random::register();

        $text = $this->createAsset('foo.txt');
        $video = $this->createAsset('foo.mp4');
        $image = $this->createAsset('foo.jpg');

        $this->assertEquals(
            ThumbnailService::generate($text),
            'https://picsum.photos/200/300'
        );

        $this->assertEquals(
            ThumbnailService::generate($video),
            'https://picsum.photos/200/300'
        );

        $this->assertEquals(
            ThumbnailService::generate($image),
            'https://picsum.photos/200/300'
        );
    }

    protected function createAsset(string $filename, string $disk = 'public')
    {
        $container = (new AssetContainer)->handle('main')->disk($disk);
        $asset = new Asset;
        $asset->container($container);
        $asset->path("img/{$filename}");

        return $asset;
    }
}
