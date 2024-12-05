<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsArrayTest extends TestCase
{
    public static function arrayProvider(): array
    {
        return [
            'empty_array' => [true, []],
            'array_with_one_item' => [true, ['foo']],
            'array_with_multiple_item' => [true, ['foo', 'bar', 'baz']],
            'multi_dimensional_array' => [true, ['foo', 'bar' => ['hello', 'world'], 'baz']],
            'no_array' => [false, '!@#'],

        ];
    }

    #[Test]
    #[DataProvider('arrayProvider')]
    public function it_returns_true_if_value_is_array($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isArray()->fetch();
    }
}
