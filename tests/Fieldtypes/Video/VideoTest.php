<?php

namespace Tests\Fieldtypes\Video;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Video\Video;
use Tests\TestCase;

class VideoTest extends TestCase
{
    #[Test]
    #[DataProvider('valuesProvider')]
    public function it_creates_video($provider, $id, $url)
    {
        $video = Video::fromUrl($url);

        $this->assertSame($provider, $video->provider);
        $this->assertSame($id, $video->id);
    }

    public static function valuesProvider()
    {
        return [
            ['Youtube', null, 'https://www.youtube.com/watch?v=FK3dav4bA4s'],
            ['Cloudflare', '1234', 'cloudflare:1234'],
        ];
    }
}
