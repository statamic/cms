<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FlattenTest extends TestCase
{
    #[Test]
    #[DataProvider('flattenProvider')]
    public function it_flattens_an_array($depth, $expected): void
    {
        $input = [
            [
                'foo',
                [
                    'bar',
                    [
                        'baz',
                    ],
                ],
            ],
            'zap',
        ];

        $modified = $this->modify($input, $depth);
        $this->assertEquals($expected, $modified);
    }

    public static function flattenProvider()
    {
        return [
            'depth null' => [null, ['foo', 'bar', 'baz', 'zap']],
            'depth 1' => [1, ['foo', ['bar', ['baz']], 'zap']],
            'depth 2' => [2, ['foo', 'bar', ['baz'], 'zap']],
            'depth 3' => [3, ['foo', 'bar', 'baz', 'zap']],
            'depth 4' => [4, ['foo', 'bar', 'baz', 'zap']],
        ];
    }

    #[Test]
    #[DataProvider('flattenWithKeysProvider')]
    public function it_flattens_an_array_with_keys($depth, $expected): void
    {
        $input = [
            [
                'foo',
                'alfa' => [
                    'bar',
                    'bravo' => [
                        'baz',
                    ],
                ],
            ],
            'zap',
        ];

        $modified = $this->modify($input, $depth);
        $this->assertEquals($expected, $modified);
    }

    public static function flattenWithKeysProvider()
    {
        return [
            'depth null' => [null, ['foo', 'bar', 'baz', 'zap']],
            'depth 1' => [1, ['foo', ['bar', 'bravo' => ['baz']], 'zap']],
            'depth 2' => [2, ['foo', 'bar', ['baz'], 'zap']],
            'depth 3' => [3, ['foo', 'bar', 'baz', 'zap']],
            'depth 4' => [4, ['foo', 'bar', 'baz', 'zap']],
        ];
    }

    private function modify(array $value, $param = null)
    {
        return Modify::value($value)->flatten($param)->fetch();
    }
}
