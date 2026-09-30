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
    #[DataProvider('idsProvider')]
    public function it_extracts_the_id_and_privacy_hash($value, $id, $privacyHash)
    {
        $video = Embed::fromValue($value);

        $this->assertSame($id, $video->id);
        $this->assertSame($privacyHash, $video->privacyHash);
    }

    public static function idsProvider()
    {
        return [
            'youtube' => ['https://www.youtube.com/watch?v=FK3dav4bA4s', 'FK3dav4bA4s', null],
            'youtube without www' => ['https://youtube.com/watch?v=FK3dav4bA4s', 'FK3dav4bA4s', null],
            'mobile youtube' => ['https://m.youtube.com/watch?v=FK3dav4bA4s', 'FK3dav4bA4s', null],
            'youtube with a start time' => ['https://www.youtube.com/watch?v=hyJ7CBs_2RQ&t=2', 'hyJ7CBs_2RQ', null],
            'youtube with v after other params' => ['https://www.youtube.com/watch?feature=share&v=FK3dav4bA4s', 'FK3dav4bA4s', null],
            'youtube shorts' => ['https://www.youtube.com/shorts/FK3dav4bA4s', 'FK3dav4bA4s', null],
            'youtube embed' => ['https://www.youtube.com/embed/FK3dav4bA4s?start=2', 'FK3dav4bA4s', null],
            'youtube nocookie embed' => ['https://www.youtube-nocookie.com/embed/FK3dav4bA4s', 'FK3dav4bA4s', null],
            'youtu.be' => ['https://youtu.be/FK3dav4bA4s', 'FK3dav4bA4s', null],
            'youtu.be with a start time' => ['https://youtu.be/s72r_wu_NVY?t=559', 's72r_wu_NVY', null],
            'youtube without a scheme' => ['www.youtube.com/watch?v=FK3dav4bA4s', 'FK3dav4bA4s', null],
            'youtube shorts without a scheme' => ['youtube.com/shorts/FK3dav4bA4s', 'FK3dav4bA4s', null],
            'youtu.be without a scheme' => ['youtu.be/FK3dav4bA4s', 'FK3dav4bA4s', null],
            'protocol-relative youtu.be' => ['//youtu.be/FK3dav4bA4s', 'FK3dav4bA4s', null],
            'youtube without an id' => ['https://www.youtube.com/@statamic', null, null],
            'youtube with a malformed id' => ['https://www.youtube.com/watch?v=x"><script>alert(1)</script>', null, null],
            'youtube with an array id' => ['https://www.youtube.com/watch?v[]=FK3dav4bA4s', null, null],
            'vimeo' => ['https://vimeo.com/22439234', '22439234', null],
            'vimeo with a query string' => ['https://vimeo.com/22439234?foo=bar', '22439234', null],
            'unlisted vimeo' => ['https://vimeo.com/735352648/fa55a4d0fc', '735352648', 'fa55a4d0fc'],
            'unlisted vimeo with a query string' => ['https://vimeo.com/735352648/fa55a4d0fc?foo=bar', '735352648', 'fa55a4d0fc'],
            'vimeo player' => ['https://player.vimeo.com/video/22439234', '22439234', null],
            'unlisted vimeo player' => ['https://player.vimeo.com/video/735352648?h=fa55a4d0fc', '735352648', 'fa55a4d0fc'],
            'vimeo progressive file' => ['https://player.vimeo.com/progressive_redirect/playback/990169258/rendition/1080p/file.mp4?loc=external', '990169258', null],
            'vimeo without a scheme' => ['vimeo.com/22439234', '22439234', null],
            'unlisted vimeo without a scheme' => ['vimeo.com/735352648/fa55a4d0fc', '735352648', 'fa55a4d0fc'],
            'vimeo player without a scheme' => ['player.vimeo.com/video/735352648?h=fa55a4d0fc', '735352648', 'fa55a4d0fc'],
            'vimeo progressive file with the api format' => ['https://player.vimeo.com/progressive_redirect/playback/286898202/container/d8f1d190-1e26-407e-90a4-45991fe334f0/7bfe0e56?expires=1686246201', '286898202', null],
            'vimeo without an id' => ['https://vimeo.com/statamic', null, null],
            'vimeo with a malformed hash' => ['https://player.vimeo.com/video/735352648?h=x"><script>', '735352648', null],
            'file' => ['https://example.com/clip.mp4', null, null],
            'unsupported' => ['https://example.com/nope', null, null],
            'null' => [null, null, null],
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
            'id' => '22439234',
            'privacy_hash' => null,
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
