<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Value;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class SumTest extends TestCase
{
    #[Test]
    #[DataProvider('sumsProvider')]
    public function it_sums($sum, $key, $array)
    {
        $this->assertSame($sum, $this->modify($array, $key));
    }

    public static function sumsProvider()
    {
        return [
            'list of ints' => [7, null, [1, 2, 3, 1]],
            'list of strings' => [7, null, ['1', '2', '3', '1']],
            'list of floats, should return an integer' => [7, null, [1.5, 2.5, 3]],
            'list of floats, should return a float' => [7.5, null, [1.5, 2, 3, 1]],
            'list of strings with points' => [7.5, null, ['1.5', '2', '3', '1.0']],
            'associative array of ints' => [7, 'foo', [
                ['foo' => 1],
                ['foo' => 2],
                ['foo' => 3],
                ['foo' => 1],
            ]],
            'associative array of strings' => [7, 'foo', [
                ['foo' => '1'],
                ['foo' => '2'],
                ['foo' => '3'],
                ['foo' => '1'],
            ]],

            'list of int values' => [7, null, [
                new Value(1),
                new Value(2),
                new Value(3),
                new Value(1),
            ]],
            'list of string values' => [7, null, [
                new Value('1'),
                new Value('2'),
                new Value('3'),
                new Value('1'),
            ]],
            'associative array of int values' => [7, 'foo', [
                ['foo' => new Value(1)],
                ['foo' => new Value(2)],
                ['foo' => new Value(3)],
                ['foo' => new Value(1)],
            ]],
            'associative array of string values' => [7, 'foo', [
                ['foo' => new Value('1')],
                ['foo' => new Value('2')],
                ['foo' => new Value('3')],
                ['foo' => new Value('1')],
            ]],
        ];
    }

    public function modify($arr, $key)
    {
        return Modify::value($arr)->sum($key)->fetch();
    }
}
