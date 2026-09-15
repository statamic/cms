<?php

namespace Tests\Fieldtypes\Video;

use Illuminate\Support\Facades\Http;
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
            'youtube' => ['https://www.youtube.com/watch?v=FK3dav4bA4s', 'Youtube', null, 'https://www.youtube-nocookie.com/embed/FK3dav4bA4s'],
            'youtube shorts' => ['https://www.youtube.com/shorts/FK3dav4bA4s', 'Youtube', null, 'https://www.youtube-nocookie.com/embed/FK3dav4bA4s'],
            'youtu.be' => ['https://youtu.be/FK3dav4bA4s', 'Youtube', null, 'https://www.youtube-nocookie.com/embed/FK3dav4bA4s'],
            'vimeo' => ['https://vimeo.com/22439234', 'Vimeo', null, 'https://player.vimeo.com/video/22439234?dnt=1'],
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
    public function it_does_not_make_http_requests_for_offline_providers()
    {
        Http::preventStrayRequests();

        $this->assertSame('Youtube', Embed::fromValue('https://www.youtube.com/watch?v=FK3dav4bA4s')->provider);
        $this->assertSame('Vimeo', Embed::fromValue('https://vimeo.com/22439234')->provider);
    }

    #[Test]
    public function it_returns_an_embed_url_rather_than_provider_supplied_markup()
    {
        Http::fake(['*' => Http::response([
            'html' => '<img src=x onerror="alert(1)"><iframe src="https://fast.wistia.net/embed/iframe/abc"></iframe>',
        ])]);

        $video = Embed::fromValue('https://wistia.com/medias/abc');

        $this->assertSame('https://fast.wistia.net/embed/iframe/abc', $video->embedUrl);
        $this->assertStringNotContainsString('onerror', $video->embedUrl);
    }

    #[Test]
    public function it_rejects_an_embed_that_is_not_a_valid_url()
    {
        Http::fake(['*' => Http::response(['html' => '<iframe src="javascript:alert(1)"></iframe>'])]);

        $this->assertFalse(Embed::fromValue('https://wistia.com/medias/abc')->isSupported());
    }

    #[Test]
    public function it_upgrades_an_insecure_embed_url_to_https()
    {
        Http::fake(['*' => Http::response(['html' => '<iframe src="http://fast.wistia.net/embed/iframe/abc"></iframe>'])]);

        $this->assertSame(
            'https://fast.wistia.net/embed/iframe/abc',
            Embed::fromValue('https://wistia.com/medias/abc')->embedUrl,
        );
    }

    #[Test]
    public function it_is_not_supported_when_the_lookup_fails()
    {
        Http::fake(['*' => Http::response(status: 500)]);

        $this->assertFalse(Embed::fromValue('https://wistia.com/medias/abc')->isSupported());
    }

    #[Test]
    public function it_caches_lookups_that_require_a_request()
    {
        Http::fake(['*' => Http::response(['html' => '<iframe src="https://fast.wistia.net/embed/iframe/abc"></iframe>'])]);

        Embed::fromValue('https://wistia.com/medias/abc');
        Embed::fromValue('https://wistia.com/medias/abc');

        Http::assertSentCount(1);
    }

    #[Test]
    public function it_casts_to_the_original_value()
    {
        $this->assertSame('https://vimeo.com/22439234', (string) Embed::fromValue('https://vimeo.com/22439234'));
        $this->assertSame('cloudflare:1234', (string) Embed::fromValue('cloudflare:1234'));
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
    public function it_serializes_to_json_as_the_original_value()
    {
        $this->assertSame('"cloudflare:1234"', json_encode(Embed::fromValue('cloudflare:1234')));
        $this->assertSame('"https:\\/\\/vimeo.com\\/1"', json_encode(Embed::fromValue('https://vimeo.com/1')));
    }
}
