<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Video;
use Statamic\Fieldtypes\Video\Video as VideoDetails;
use Tests\TestCase;

class VideoTest extends TestCase
{
    #[Test]
    public function it_preloads_empty_field()
    {
        $meta = $this->fieldtype()->preload();

        $this->assertArrayNotHasKey('video', $meta);
        $this->assertContains(['value' => 'Youtube', 'label' => 'Youtube'], $meta['providers']);
        $this->assertContains(['value' => 'cloudflare', 'label' => 'Cloudflare Stream'], $meta['providers']);
    }

    #[Test]
    #[DataProvider('preloadValuesProvider')]
    public function it_preloads_with_value($provider, $embedUrl, $value)
    {
        $meta = $this->fieldtype($value)->preload();

        $this->assertSame($provider, $meta['video']['provider']);
        $this->assertSame($embedUrl, $meta['video']['embed_url']);
    }

    public static function preloadValuesProvider()
    {
        return [
            'youtube' => ['Youtube', 'https://www.youtube.com/embed/FK3dav4bA4s?feature=oembed', 'https://www.youtube.com/watch?v=FK3dav4bA4s'],
            'cloudflare' => ['cloudflare', 'https://iframe.cloudflarestream.com/1234', 'cloudflare:1234'],
            'file' => ['file', 'https://example.com/clip.mp4', 'https://example.com/clip.mp4'],
        ];
    }

    #[Test]
    public function it_augments_null_to_null()
    {
        $this->assertNull($this->fieldtype()->augment(null));
    }

    #[Test]
    #[DataProvider('augmentProvider')]
    public function it_augments_to_a_video($value, $provider, $id, $embedUrl)
    {
        $video = $this->fieldtype()->augment($value);

        $this->assertInstanceOf(VideoDetails::class, $video);
        $this->assertSame($provider, $video->provider);
        $this->assertSame($id, $video->id);
        $this->assertSame($embedUrl, $video->embedUrl);
    }

    public static function augmentProvider()
    {
        return [
            'url' => ['https://vimeo.com/22439234', 'Vimeo', null, 'https://player.vimeo.com/video/22439234'],
            'cloudflare' => ['cloudflare:1234', 'cloudflare', '1234', 'https://iframe.cloudflarestream.com/1234'],
            'unsupported' => ['https://example.com/nope', 'unsupported', null, null],
        ];
    }

    #[Test]
    public function the_augmented_value_casts_to_the_original_url_for_backwards_compatibility()
    {
        $this->assertSame(
            'https://vimeo.com/22439234',
            (string) $this->fieldtype()->augment('https://vimeo.com/22439234'),
        );
    }

    private function fieldtype($value = null)
    {
        return tap(new Video, fn (Video $fieldtype) => $fieldtype
            ->setField(new Field('test', ['type' => 'video']))
            ->field()->setValue($value)
        );
    }
}
