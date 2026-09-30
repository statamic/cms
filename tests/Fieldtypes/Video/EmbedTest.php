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
    public function it_creates_a_video($value, $provider, $embedUrl)
    {
        $video = Embed::fromValue($value);

        $this->assertSame($provider, $video->provider);
        $this->assertSame($embedUrl, $video->embedUrl);
    }

    public static function valuesProvider()
    {
        return [
            'youtube' => ['https://www.youtube.com/watch?v=FK3dav4bA4s', 'youtube', 'https://www.youtube-nocookie.com/embed/FK3dav4bA4s'],
            'youtube shorts' => ['https://www.youtube.com/shorts/FK3dav4bA4s', 'youtube', 'https://www.youtube-nocookie.com/embed/FK3dav4bA4s'],
            'youtu.be' => ['https://youtu.be/FK3dav4bA4s', 'youtube', 'https://www.youtube-nocookie.com/embed/FK3dav4bA4s'],
            'vimeo' => ['https://vimeo.com/22439234', 'vimeo', 'https://player.vimeo.com/video/22439234?dnt=1'],
            'mp4 file' => ['https://example.com/clip.mp4', 'file', 'https://example.com/clip.mp4'],
            'uppercase file extension' => ['https://example.com/clip.MOV', 'file', 'https://example.com/clip.MOV'],
            'file with a query string' => ['https://example.com/clip.webm?t=1', 'file', 'https://example.com/clip.webm?t=1'],
            'unsupported' => ['https://example.com/nope', 'unsupported', null],
            'empty' => ['', 'unsupported', null],
            'null' => [null, 'unsupported', null],
        ];
    }

    #[Test]
    public function it_casts_to_the_original_value()
    {
        $this->assertSame('https://vimeo.com/22439234', (string) Embed::fromValue('https://vimeo.com/22439234'));
        $this->assertSame('https://example.com/nope', (string) Embed::fromValue('https://example.com/nope'));
        $this->assertSame('', (string) Embed::fromValue(null));
    }

    #[Test]
    public function it_is_truthy_whenever_it_holds_a_value()
    {
        $this->assertTrue(Embed::fromValue('https://vimeo.com/22439234')->toBool());
        $this->assertTrue(Embed::fromValue('https://example.com/nope')->toBool());
        $this->assertFalse(Embed::fromValue('')->toBool());
    }

    #[Test]
    public function it_knows_whether_it_is_supported_and_embeddable()
    {
        $this->assertTrue(Embed::fromValue('https://vimeo.com/22439234')->isEmbeddable());
        $this->assertTrue(Embed::fromValue('https://vimeo.com/22439234')->isSupported());

        // A file is something we can play, but not something we can put in an iframe.
        $this->assertFalse(Embed::fromValue('https://example.com/clip.mp4')->isEmbeddable());
        $this->assertTrue(Embed::fromValue('https://example.com/clip.mp4')->isSupported());

        $this->assertFalse(Embed::fromValue('https://example.com/nope')->isEmbeddable());
        $this->assertFalse(Embed::fromValue('https://example.com/nope')->isSupported());
    }

    #[Test]
    public function it_is_arrayable_and_accessible_as_an_array()
    {
        $video = Embed::fromValue('https://vimeo.com/22439234');

        $this->assertSame([
            'embed_url' => 'https://player.vimeo.com/video/22439234?dnt=1',
            'provider' => 'vimeo',
            'url' => 'https://vimeo.com/22439234',
        ], $video->toArray());

        $this->assertSame('https://player.vimeo.com/video/22439234?dnt=1', $video['embed_url']);
        $this->assertTrue(isset($video['provider']));
        $this->assertNull($video['nope']);
    }

    #[Test]
    public function it_serializes_to_json_as_the_original_value()
    {
        $this->assertSame('"https:\\/\\/vimeo.com\\/1"', json_encode(Embed::fromValue('https://vimeo.com/1')));
    }
}
