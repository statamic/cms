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
