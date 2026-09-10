<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Video;
use Tests\TestCase;

class VideoTest extends TestCase
{
    #[Test]
    public function it_preloads_empty_field()
    {
        $fieldtype = (new Video)->setField(new Field('test', ['type' => 'video']));

        $meta = $fieldtype->preload();

        $this->assertSame('Cloudflare', $meta['providers'][0]['provider']);
        $this->assertFalse(isset($meta['provider']));
    }

    #[Test]
    #[DataProvider('preloadValuesProvider')]
    public function it_preloads_with_value($provider, $value)
    {
        $fieldtype = tap(new Video, fn (Video $v) => $v
            ->setField(new Field('test', ['type' => 'video']))
            ->field()->setValue($value)
        );

        $this->assertSame($provider, $fieldtype->preload()['provider']);
    }

    public static function preloadValuesProvider()
    {
        return [
            ['Youtube', 'https://www.youtube.com/watch?v=FK3dav4bA4s'],
            ['Cloudflare', 'cloudflare:1234'],
        ];
    }
}
