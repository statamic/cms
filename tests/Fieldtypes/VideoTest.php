<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
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
    public function it_augments_to_a_video($value, $provider, $embedUrl)
    {
        $video = $this->fieldtype()->augment($value);

        $this->assertInstanceOf(Embed::class, $video);
        $this->assertSame($provider, $video->provider());
        $this->assertSame($embedUrl, $video->embedUrl());
    }

    public static function augmentProvider()
    {
        return [
            'url' => ['https://vimeo.com/22439234', 'vimeo', 'https://player.vimeo.com/video/22439234?dnt=1'],
            'file' => ['https://example.com/clip.mp4', 'file', 'https://example.com/clip.mp4'],
            'unsupported' => ['https://example.com/nope', 'unsupported', null],
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
    #[DataProvider('emptyFallbackProvider')]
    public function an_empty_video_falls_back_in_antlers($template)
    {
        $this->assertSame('fb', $this->render($template, ''));
    }

    public static function emptyFallbackProvider()
    {
        return [
            'null coalescence' => ['{{ video ?? "fb" }}'],
            'ternary' => ['{{ video ?: "fb" }}'],
            'or' => ['{{ video or "fb" }}'],
            'equals null' => ['{{ if video == null }}fb{{ /if }}'],
        ];
    }

    #[Test]
    public function a_video_renders_in_antlers()
    {
        $this->assertSame(
            'https://vimeo.com/22439234 vimeo 22439234',
            $this->render('{{ video ?? "fb" }} {{ video:provider }} {{ video:id }}', 'https://vimeo.com/22439234'),
        );
    }

    private function fieldtype()
    {
        return (new Video)->setField(new Field('test', ['type' => 'video']));
    }

    private function render(string $template, ?string $value): string
    {
        return (string) Antlers::parse($template, ['video' => new Value($value, 'video', $this->fieldtype())]);
    }
}
