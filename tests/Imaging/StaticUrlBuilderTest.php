<?php

namespace Tests\Imaging;

use League\Glide\Server;
use Mockery as m;
use Statamic\Imaging\ImageGenerator;
use Statamic\Imaging\StaticUrlBuilder;
use Tests\TestCase;

class StaticUrlBuilderTest extends TestCase
{
    /**
     * @var StaticUrlBuilder
     */
    protected $builder;

    /**
     * @var \Mockery\MockInterface
     */
    protected $generator;

    /**
     * @var Server
     */
    protected $server;

    public function setUp(): void
    {
        parent::setUp();

        $this->generator = m::mock(ImageGenerator::class);
        $this->server = app(Server::class);

        $this->builder = new StaticUrlBuilder($this->generator, [
            'route' => '/img',
        ]);
    }

    public function testPath()
    {
        $path = $this->server->getCachePath('foo.jpg', ['w' => '100']);

        $this->generator->shouldReceive('generateByPath')->andReturn($path);

        $this->assertEquals(
            '/img/'.ltrim($path, '/'),
            $this->builder->build('/foo.jpg', ['w' => '100'])
        );
    }

    public function testFilenameHasNoAffect()
    {
        $path = $this->server->getCachePath('foo.jpg', ['w' => '100']);

        $this->generator->shouldReceive('generateByPath')->andReturn($path);

        $this->assertEquals(
            '/img/'.ltrim($path, '/'),
            $this->builder->build('/foo.jpg', ['w' => '100'], 'custom.jpg')
        );
    }

    public function testUnknownAssetThrowsException()
    {
        $this->expectException('Statamic\Imaging\AssetNotFoundException');
        $this->expectExceptionMessage('Could not generate a static manipulated image URL from asset [123]');

        $this->builder->build('123', ['w' => '100']);
    }
}
