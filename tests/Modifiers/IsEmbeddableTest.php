<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsEmbeddableTest extends TestCase
{
    public static function embeddablesProvider(): array
    {
        return [
            'youtube.com' => [true, 'https://www.youtube.com/watch?v=s9F5fhJQo34'],
            'youtu.be' => [true, 'https://www.youtu.be/watch?v=s9F5fhJQo34'],
            'vimeo' => [true, 'https://vimeo.com/22439234'],
            'cloudflare' => [true, 'https://customer-wve7oze7jjxdb0j6.cloudflarestream.com/b209b1484821fff0ed2e5535fbb4ff7b/manifest/video.m3u8'],
            'cloudflare without an id' => [false, 'https://customer-wve7oze7jjxdb0j6.cloudflarestream.com/'],
            'other' => [false, 'http://video-home-system.com/video.mp4'],
        ];
    }

    #[Test]
    #[DataProvider('embeddablesProvider')]
    public function it_checks_if_an_url_is_embeddable($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->isEmbeddable()->fetch();
    }
}
