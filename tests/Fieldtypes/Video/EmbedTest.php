<?php

namespace Tests\Fieldtypes\Video;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Video\Embed;
use Tests\TestCase;

class EmbedTest extends TestCase
{
    #[Test]
    #[DataProvider('valuesProvider')]
    public function it_creates_a_video($value, $provider, $id, $embedUrl)
    {
        $video = Embed::fromValue($value);

        $this->assertSame($provider, $video->provider);
        $this->assertSame($id, $video->id);
        $this->assertSame($embedUrl, $video->embedUrl);
    }

    public static function valuesProvider()
    {
        return [
            'youtube' => ['https://www.youtube.com/watch?v=FK3dav4bA4s', 'youtube', null, 'https://www.youtube-nocookie.com/embed/FK3dav4bA4s'],
            'youtube shorts' => ['https://www.youtube.com/shorts/FK3dav4bA4s', 'youtube', null, 'https://www.youtube-nocookie.com/embed/FK3dav4bA4s'],
            'youtu.be' => ['https://youtu.be/FK3dav4bA4s', 'youtube', null, 'https://www.youtube-nocookie.com/embed/FK3dav4bA4s'],
            'vimeo' => ['https://vimeo.com/22439234', 'vimeo', null, 'https://player.vimeo.com/video/22439234?dnt=1'],
            'cloudflare' => ['cloudflare:1234', 'cloudflare', '1234', 'https://iframe.cloudflarestream.com/1234'],
            'cloudflare without an id' => ['cloudflare:', 'unsupported', null, null],
            'cloudflare with a malformed id' => ['cloudflare:1234"></iframe><script>alert(1)</script>', 'unsupported', null, null],
            'cloudflare with a path traversal id' => ['cloudflare:../../evil', 'unsupported', null, null],
            'mp4 file' => ['https://example.com/clip.mp4', 'file', null, 'https://example.com/clip.mp4'],
            'uppercase file extension' => ['https://example.com/clip.MOV', 'file', null, 'https://example.com/clip.MOV'],
            'file with a query string' => ['https://example.com/clip.webm?t=1', 'file', null, 'https://example.com/clip.webm?t=1'],
            'unsupported' => ['https://example.com/nope', 'unsupported', null, null],
            'empty' => ['', 'unsupported', null, null],
            'null' => [null, 'unsupported', null, null],
        ];
    }

    #[Test]
    public function it_casts_to_the_original_value()
    {
        $this->assertSame('https://vimeo.com/22439234', (string) Embed::fromValue('https://vimeo.com/22439234'));
        $this->assertSame('cloudflare:1234', (string) Embed::fromValue('cloudflare:1234'));
        $this->assertSame('', (string) Embed::fromValue(null));
    }

    #[Test]
    public function it_is_falsey_when_unsupported()
    {
        $this->assertTrue(Embed::fromValue('https://vimeo.com/22439234')->toBool());
        $this->assertFalse(Embed::fromValue('https://example.com/nope')->toBool());
    }

    #[Test]
    public function it_is_arrayable_and_accessible_as_an_array()
    {
        $video = Embed::fromValue('cloudflare:1234');

        $this->assertSame([
            'embed_url' => 'https://iframe.cloudflarestream.com/1234',
            'id' => '1234',
            'provider' => 'cloudflare',
            'url' => 'cloudflare:1234',
        ], $video->toArray());

        $this->assertSame('https://iframe.cloudflarestream.com/1234', $video['embed_url']);
        $this->assertTrue(isset($video['provider']));
        $this->assertNull($video['nope']);
    }

    #[Test]
    public function it_serializes_to_json_as_its_array()
    {
        $this->assertSame(
            json_encode(Embed::fromValue('cloudflare:1234')->toArray()),
            json_encode(Embed::fromValue('cloudflare:1234')),
        );
    }
}
