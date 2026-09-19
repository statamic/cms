<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Video;
use Statamic\Fieldtypes\Video\Embed;
use Tests\TestCase;

class VideoTest extends TestCase
{
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

        $this->assertInstanceOf(Embed::class, $video);
        $this->assertSame($provider, $video->provider);
        $this->assertSame($id, $video->id);
        $this->assertSame($embedUrl, $video->embedUrl);
    }

    public static function augmentProvider()
    {
        return [
            'url' => ['https://vimeo.com/22439234', 'vimeo', null, 'https://player.vimeo.com/video/22439234?dnt=1'],
            'cloudflare' => ['cloudflare:1234', 'cloudflare', '1234', 'https://iframe.cloudflarestream.com/1234'],
            'file' => ['https://example.com/clip.mp4', 'file', null, 'https://example.com/clip.mp4'],
            'unsupported' => ['https://example.com/nope', 'unsupported', null, null],
        ];
    }

    #[Test]
    public function the_augmented_value_casts_to_the_original_value_for_backwards_compatibility()
    {
        $this->assertSame(
            'https://vimeo.com/22439234',
            (string) $this->fieldtype()->augment('https://vimeo.com/22439234'),
        );
    }

    #[Test]
    public function it_preloads_the_providers_and_the_current_video()
    {
        $meta = $this->fieldtype('cloudflare:1234')->preload();

        $this->assertSame([
            ['value' => 'url', 'label' => 'URL'],
            ['value' => 'cloudflare', 'label' => 'Cloudflare Stream'],
        ], $meta['providers']);

        $this->assertSame('cloudflare', $meta['video']['provider']);
        $this->assertSame('https://iframe.cloudflarestream.com/1234', $meta['video']['embed_url']);
    }

    #[Test]
    public function it_preloads_an_empty_field()
    {
        $this->assertSame('unsupported', $this->fieldtype()->preload()['video']['provider']);
    }

    private function fieldtype($value = null)
    {
        return tap(new Video, fn (Video $fieldtype) => $fieldtype
            ->setField(new Field('test', ['type' => 'video']))
            ->field()->setValue($value)
        );
    }
}
