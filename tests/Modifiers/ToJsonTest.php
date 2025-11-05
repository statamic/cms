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
