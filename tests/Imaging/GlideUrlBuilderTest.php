<?php

namespace Tests\Imaging;

use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Imaging\GlideUrlBuilder;
use Statamic\Support\Str;
use Tests\TestCase;

class GlideUrlBuilderTest extends TestCase
{
    /**
     * @var GlideUrlBuilder
     */
    protected $builder;

    public function setUp(): void
    {
        parent::setUp();

        $this->builder = new GlideUrlBuilder([
            'key' => null,
            'route' => 'img',
        ]);
    }

    public function testPath()
    {
        $this->assertEquals(
            '/img/foo.jpg?w=100',
            $this->builder->build('/foo.jpg', ['w' => '100'])
        );
    }

    public function testPathWithSpace()
    {
        $this->assertEquals(
            '/img/foo%20bar.jpg?w=100',
            $this->builder->build('/foo bar.jpg', ['w' => '100'])
        );
    }

    public function testExternal()
    {
        $this->assertEquals(
            '/img/http/'.base64_encode('http://example.com').'/example.com?w=100',
            $this->builder->build('http://example.com', ['w' => '100'])
        );
    }

    public function testAsset()
    {
        $asset = new Asset;
        $asset->container((new AssetContainer)->handle('main'));
        $asset->path('img/foo.jpg');

        $encoded = Str::toBase64Url('main/img/foo.jpg');

        $this->assertEquals(
            "/img/asset/$encoded/foo.jpg?w=100",
            $this->builder->build($asset, ['w' => '100'])
        );
    }

    public function testId()
    {
        $encoded = Str::toBase64Url('main/img/foo.jpg');

        $this->assertEquals(
            "/img/asset/$encoded?w=100",
            $this->builder->build('main::img/foo.jpg', ['w' => '100'])
        );
    }

    public function testConfigAddsFilename()
    {
        $asset = new Asset;
        $asset->container((new AssetContainer)->handle('main'));
        $asset->path('img/foo.jpg');

        $encoded = Str::toBase64Url('main/img/foo.jpg');

        $this->assertEquals(
            "/img/asset/$encoded/foo.jpg?w=100",
            $this->builder->build($asset, ['w' => '100'])
        );
    }

    public function testMarkWithAsset()
    {
        $asset = new Asset;
        $asset->container((new AssetContainer)->handle('main'));
        $asset->path('img/foo.jpg');

        $encoded = rawurlencode(Str::toBase64Url('main/img/foo.jpg'));

        $this->assertEquals(
            "/img/foo.jpg?w=100&mark=asset%3A%3A$encoded",
            $this->builder->build('/foo.jpg', ['w' => '100', 'mark' => $asset])
        );
    }
}
