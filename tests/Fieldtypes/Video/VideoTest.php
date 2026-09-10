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

    #[Test]
    public function it_escapes_the_cloudflare_id_in_the_embed()
    {
        $video = Video::fromUrl('cloudflare:1234"></iframe><script>alert(1)</script>');

        $this->assertStringNotContainsString('<script>', $video->embed);
        $this->assertStringContainsString(
            'src="https://iframe.cloudflarestream.com/1234&quot;&gt;&lt;/iframe&gt;&lt;script&gt;alert(1)&lt;/script&gt;"',
            $video->embed
        );
    }
}
