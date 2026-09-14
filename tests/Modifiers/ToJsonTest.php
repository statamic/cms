<?php

namespace Tests\Modifiers;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Entry;
use Statamic\Modifiers\Modify;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ToJsonTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('bourneJsonBourneProvider')]
    public function it_converts_to_json($input, $expected): void
    {
        $modified = $this->modify(value($input));

        $this->assertEquals($expected, $modified);
    }

    #[Test]
    #[DataProvider('bourneJsonBourneProvider')]
    public function it_pretty_prints($input, $expected): void
    {
        $modified = $this->modify(value($input), ['pretty']);

        $this->assertEquals(json_encode(json_decode($expected, true), JSON_PRETTY_PRINT), $modified);
    }

    #[Test]
    public function it_hex_encodes_html_sensitive_characters_when_safe(): void
    {
        $value = '</script><script>alert(1)</script>';
        $modified = $this->modify($value, ['safe']);

        $this->assertSame(
            json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            $modified,
        );
        $this->assertStringNotContainsString('</script>', $modified);
        $this->assertSame($value, json_decode($modified));
    }

    #[Test]
    #[DataProvider('safeParamOrderProvider')]
    public function it_can_combine_pretty_and_safe(array $params): void
    {
        $value = ['html' => '</script>'];
        $modified = $this->modify($value, $params);

        $this->assertSame(
            json_encode($value, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            $modified,
        );
        $this->assertStringNotContainsString('</script>', $modified);
        $this->assertSame($value, json_decode($modified, true));
    }

    public static function safeParamOrderProvider(): array
    {
        return [
            'pretty then safe' => [['pretty', 'safe']],
            'safe then pretty' => [['safe', 'pretty']],
        ];
    }

    private function modify($value, $options = [])
    {
        return Modify::value($value)->toJson($options)->fetch();
    }

    public static function bourneJsonBourneProvider(): array
    {
        return [
            'empty array' => [[], '[]'],
            'array' => [['book' => 'All The Places You\'ll Go'], '{"book":"All The Places You\'ll Go"}'],
            'string' => ['foo bar baz', '"foo bar baz"'],
            'null' => [null, 'null'],
            'collection' => [collect(['book' => 'All The Places You\'ll Go']), '{"book":"All The Places You\'ll Go"}'],
            'collection with JsonSerializables' => [
                collect([
                    new class implements \JsonSerializable
                    {
                        public function jsonSerialize(): array
                        {
                            return ['book' => 'All The Places You\'ll Go'];
                        }
                    },
                    new class implements \JsonSerializable
                    {
                        public function jsonSerialize(): array
                        {
                            return ['book' => 'Oh, The Places You\'ll Go'];
                        }
                    },
                ]), '[{"book":"All The Places You\'ll Go"},{"book":"Oh, The Places You\'ll Go"}]',
            ],
            'JsonSerializable object' => [
                new class implements \JsonSerializable
                {
                    public function jsonSerialize(): array
                    {
                        return ['book' => 'All The Places You\'ll Go'];
                    }
                }, '{"book":"All The Places You\'ll Go"}',
            ],
            'query builder' => [
                function () {
                    EntryFactory::collection('blog')->data(['title' => 'Post One'])->create();
                    EntryFactory::collection('blog')->data(['title' => 'Post Two'])->create();

                    return Entry::query()->get(['title']);
                }, '[{"title":"Post One"},{"title":"Post Two"}]',
            ],
        ];
    }
}
