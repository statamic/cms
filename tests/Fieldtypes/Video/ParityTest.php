<?php

namespace Tests\Fieldtypes\Video;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Video;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * An augmented video field has to behave like the string it was before it was
 * augmented. Everything here would pass against a raw string, so each case
 * asserts the augmented value agrees with it.
 *
 * Cloudflare values are deliberately absent: `cloudflare:<id>` is a new format
 * with no prior behaviour to preserve. VideoTest covers it instead.
 */
class ParityTest extends TestCase
{
    public static function valuesProvider()
    {
        return [
            'null' => [null],
            'empty' => [''],
            'unsupported' => ['https://example.com/nope'],
            'youtube' => ['https://www.youtube.com/watch?v=FK3dav4bA4s'],
            'youtube shorts' => ['https://www.youtube.com/shorts/FK3dav4bA4s'],
            'youtu.be' => ['https://youtu.be/FK3dav4bA4s'],
            'vimeo' => ['https://vimeo.com/22439234'],
            'unlisted vimeo' => ['https://vimeo.com/22439234/abcdef'],
            'vimeo progressive file' => ['https://player.vimeo.com/progressive_redirect/playback/123/rendition/1080p/file.mp4?loc=external'],
            'video file' => ['https://example.com/clip.mp4'],
        ];
    }

    #[Test]
    #[DataProvider('valuesProvider')]
    public function the_embed_url_modifier_matches_the_raw_value($value)
    {
        $this->assertSame(
            Modify::value($value)->embedUrl()->fetch(),
            Modify::value($this->augment($value))->embedUrl()->fetch(),
        );
    }

    #[Test]
    #[DataProvider('valuesProvider')]
    public function the_trackable_embed_url_modifier_matches_the_raw_value($value)
    {
        $this->assertSame(
            Modify::value($value)->trackableEmbedUrl()->fetch(),
            Modify::value($this->augment($value))->trackableEmbedUrl()->fetch(),
        );
    }

    #[Test]
    #[DataProvider('valuesProvider')]
    public function the_is_embeddable_modifier_matches_the_raw_value($value)
    {
        $this->assertSame(
            Modify::value($value)->isEmbeddable()->fetch(),
            Modify::value($this->augment($value))->isEmbeddable()->fetch(),
        );
    }

    #[Test]
    #[DataProvider('valuesProvider')]
    public function it_casts_to_the_original_value($value)
    {
        $this->assertSame((string) $value, (string) $this->augment($value));
    }

    #[Test]
    #[DataProvider('valuesProvider')]
    public function it_is_as_truthy_as_the_original_value($value)
    {
        // A null value isn't augmented at all, so there's no object to ask.
        if (is_null($augmented = $this->augment($value))) {
            $this->assertNull($value);

            return;
        }

        $this->assertSame((bool) $value, $augmented->toBool());
    }

    #[Test]
    #[DataProvider('valuesProvider')]
    public function it_serializes_to_the_original_value($value)
    {
        $field = (new Video)->setField(new Field('test', ['type' => 'video']));

        $this->assertSame(json_encode($value), json_encode(new Value($value, 'test', $field)));
    }

    private function augment($value)
    {
        return (new Video)->setField(new Field('test', ['type' => 'video']))->augment($value);
    }
}
