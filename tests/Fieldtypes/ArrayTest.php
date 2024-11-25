<?php

namespace Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Arr;
use Tests\TestCase;

class ArrayTest extends TestCase
{
    #[Test]
    #[DataProvider('keyedPreloadProvider')]
    public function it_preloads_keys($options, $expected)
    {
        $field = new Field('test', ['type' => 'array', 'keys' => $options]);

        $this->assertEquals($expected, $field->meta()['keys']->all());
    }

    public static function keyedPreloadProvider()
    {
        return [
            'dynamic null' => [
                null,
                [],
            ],
            'dynamic empty array' => [
                [],
                [],
            ],
            'associative array options' => [
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
            ],
            'multidimensional array options' => [
                [
                    ['key' => 'food', 'value' => 'Food'],
                    ['key' => 'drink', 'value' => 'Drink'],
                    ['key' => 'side', 'value' => 'Side'],
                ],
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
            ],
            'multidimensional array with numbers' => [
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    0 => 'Zero',
                    1 => 'One',
                    2 => 'Two',
                ],
            ],
            'multidimensional array with non-sequential numbers' => [
                [
                    ['key' => 2, 'value' => 'Two'],
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                ],
                [
                    2 => 'Two',
                    0 => 'Zero',
                    1 => 'One',
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('dynamicPreprocessProvider')]
    public function it_preprocesses_dynamic($value, $expected)
    {
        $field = new Field('test', ['type' => 'array']);

        $field->setValue($value);

        $this->assertEquals($expected, $field->preProcess()->value());
    }

    public static function dynamicPreprocessProvider()
    {
        return [
            'associative array value' => [
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array value' => [
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                    ['key' => 'side', 'value' => 'fries'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array with numbers' => [
                [
                    ['key' => 0, 'value' => 'none'],
                    ['key' => 1, 'value' => 'some'],
                    ['key' => 2, 'value' => 'more'],
                ],
                [
                    0 => 'none',
                    1 => 'some',
                    2 => 'more',
                ],
            ],
            'multidimensional array with non-sequential numbers' => [
                [
                    ['key' => 2, 'value' => 'some'],
                    ['key' => 1, 'value' => 'one'],
                    ['key' => 0, 'value' => 'none'],
                ],
                [
                    2 => 'some',
                    1 => 'one',
                    0 => 'none',
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('keyedPreprocessProvider')]
    public function it_preprocesses_keyed($options, $value, $expected)
    {
        $field = new Field('test', ['type' => 'array', 'keys' => $options]);

        $field->setValue($value);

        $this->assertEquals($expected, $field->preProcess()->value());
    }

    public static function keyedPreprocessProvider()
    {
        return [
            'associative array options, associative array value' => [
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array options, associative array value' => [
                [
                    ['key' => 'food', 'value' => 'Food'],
                    ['key' => 'drink', 'value' => 'Drink'],
                    ['key' => 'side', 'value' => 'Side'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'associative array options, multidimensional array value' => [
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                    ['key' => 'side', 'value' => 'fries'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array options, multidimensional array value' => [
                [
                    ['key' => 'food', 'value' => 'Food'],
                    ['key' => 'drink', 'value' => 'Drink'],
                    ['key' => 'side', 'value' => 'Side'],
                ],
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                    ['key' => 'side', 'value' => 'fries'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array with numbers' => [
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    ['key' => 0, 'value' => 'none'],
                    ['key' => 1, 'value' => 'some'],
                    ['key' => 2, 'value' => 'more'],
                ],
                [
                    0 => 'none',
                    1 => 'some',
                    2 => 'more',
                ],
            ],
            'multidimensional array with non-sequential numbers' => [
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    ['key' => 2, 'value' => 'some'],
                    ['key' => 1, 'value' => 'one'],
                    ['key' => 0, 'value' => 'none'],
                ],
                [
                    2 => 'some',
                    1 => 'one',
                    0 => 'none',
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('dynamicProcessProvider')]
    public function it_processes_dynamic($expand, $value, $expected)
    {
        $field = new Field('test', ['type' => 'array', 'expand' => $expand]);

        $field->setValue($value);

        $this->assertEquals($expected, $field->process()->value());
    }

    public static function dynamicProcessProvider()
    {
        return [
            'null' => [
                false,
                null,
                null,
            ],
            'string keys' => [
                false,
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'string keys with expanded setting' => [
                true,
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                    ['key' => 'side', 'value' => 'fries'],
                ],
            ],
            'numeric keys' => [
                false,
                [
                    0 => 'none',
                    1 => 'some',
                    2 => 'more',
                ],
                [
                    0 => 'none',
                    1 => 'some',
                    2 => 'more',
                ],
            ],
            'numeric keys with expanded setting' => [
                true,
                [
                    0 => 'none',
                    1 => 'some',
                    2 => 'more',
                ],
                [
                    ['key' => 0, 'value' => 'none'],
                    ['key' => 1, 'value' => 'some'],
                    ['key' => 2, 'value' => 'more'],
                ],
            ],
            'non-sequential numeric keys' => [
                false,
                [
                    2 => 'some',
                    1 => 'one',
                    0 => 'none',
                ],
                [
                    2 => 'some',
                    1 => 'one',
                    0 => 'none',
                ],
            ],
            'non-sequential numeric keys with expanded setting' => [
                true,
                [
                    2 => 'some',
                    1 => 'one',
                    0 => 'none',
                ],
                [
                    ['key' => 2, 'value' => 'some'],
                    ['key' => 1, 'value' => 'one'],
                    ['key' => 0, 'value' => 'none'],
                ],
            ],
            'strings and numeric keys' => [
                false,
                [
                    'one' => 'One',
                    2 => 'Two',
                    'three' => 'Three',
                ],
                [
                    'one' => 'One',
                    2 => 'Two',
                    'three' => 'Three',
                ],
            ],
            'strings and numeric keys with expanded setting' => [
                true,
                [
                    'one' => 'One',
                    2 => 'Two',
                    'three' => 'Three',
                ],
                [
                    ['key' => 'one', 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                    ['key' => 'three', 'value' => 'Three'],
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('keyedProcessProvider')]
    public function it_processes_keyed($expand, $options, $value, $expected)
    {
        $field = new Field('test', ['type' => 'array', 'keys' => $options, 'expand' => $expand]);

        $field->setValue($value);

        $this->assertEquals($expected, $field->process()->value());
    }

    public static function keyedProcessProvider()
    {
        return [
            'null' => [
                false,
                ['foo' => 'Foo'],
                null,
                null,
            ],
            'associative array options, associative array value' => [
                false,
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'associative array options, associative array value, with nulls' => [
                false,
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => null,
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                ],
            ],
            'associative array options, associative array value with expanded setting' => [
                true,
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                    ['key' => 'side', 'value' => 'fries'],
                ],
            ],
            'associative array options, associative array value with expanded setting, with nulls' => [
                true,
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => null,
                ],
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                ],
            ],
            'multidimensional array options, associative array value' => [
                false,
                [
                    ['key' => 'food', 'value' => 'Food'],
                    ['key' => 'drink', 'value' => 'Drink'],
                    ['key' => 'side', 'value' => 'Side'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array options, associative array value, with nulls' => [
                false,
                [
                    ['key' => 'food', 'value' => 'Food'],
                    ['key' => 'drink', 'value' => 'Drink'],
                    ['key' => 'side', 'value' => 'Side'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => null,
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                ],
            ],
            'multidimensional array options, associative array value with expanded setting' => [
                true,
                [
                    ['key' => 'food', 'value' => 'Food'],
                    ['key' => 'drink', 'value' => 'Drink'],
                    ['key' => 'side', 'value' => 'Side'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                    ['key' => 'side', 'value' => 'fries'],
                ],
            ],
            'multidimensional array options, associative array value with expanded setting, with nulls' => [
                true,
                [
                    ['key' => 'food', 'value' => 'Food'],
                    ['key' => 'drink', 'value' => 'Drink'],
                    ['key' => 'side', 'value' => 'Side'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => null,
                ],
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                ],
            ],
            'multidimensional array with numbers' => [
                false,
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    0 => 'none',
                    1 => 'some',
                    2 => 'more',
                ],
                [
                    0 => 'none',
                    1 => 'some',
                    2 => 'more',
                ],
            ],
            'multidimensional array with numbers, with nulls' => [
                false,
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    0 => 'none',
                    1 => null,
                    2 => 'more',
                ],
                [
                    0 => 'none',
                    2 => 'more',
                ],
            ],
            'multidimensional array with numbers with expanded setting' => [
                true,
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    0 => 'none',
                    1 => 'some',
                    2 => 'more',
                ],
                [
                    ['key' => 0, 'value' => 'none'],
                    ['key' => 1, 'value' => 'some'],
                    ['key' => 2, 'value' => 'more'],
                ],
            ],
            'multidimensional array with numbers with expanded setting, with nulls' => [
                true,
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    0 => 'none',
                    1 => null,
                    2 => 'more',
                ],
                [
                    ['key' => 0, 'value' => 'none'],
                    ['key' => 2, 'value' => 'more'],
                ],
            ],
            'multidimensional array with non-sequential numbers' => [
                false,
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    2 => 'some',
                    1 => 'one',
                    0 => 'none',
                ],
                [
                    2 => 'some',
                    1 => 'one',
                    0 => 'none',
                ],
            ],
            'multidimensional array with non-sequential numbers, with nulls' => [
                false,
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    2 => 'some',
                    1 => null,
                    0 => 'none',
                ],
                [
                    2 => 'some',
                    0 => 'none',
                ],
            ],
            'multidimensional array with non-sequential numbers with expanded setting' => [
                true,
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    2 => 'some',
                    1 => 'one',
                    0 => 'none',
                ],
                [
                    ['key' => 2, 'value' => 'some'],
                    ['key' => 1, 'value' => 'one'],
                    ['key' => 0, 'value' => 'none'],
                ],
            ],
            'multidimensional array with non-sequential numbers with expanded setting, with nulls' => [
                true,
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    2 => 'some',
                    1 => null,
                    0 => 'none',
                ],
                [
                    ['key' => 2, 'value' => 'some'],
                    ['key' => 0, 'value' => 'none'],
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('dynamicAugmentProvider')]
    public function it_augments_dynamic($value, $expected)
    {
        $fieldtype = (new Arr)->setField(new Field('test', ['type' => 'arr']));

        $this->assertEquals($expected, $fieldtype->augment($value));
    }

    public static function dynamicAugmentProvider()
    {
        return [
            'null' => [
                null,
                null,
            ],
            'associative array value' => [
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array value' => [
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                    ['key' => 'side', 'value' => 'fries'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array with numbers' => [
                [
                    ['key' => 0, 'value' => 'none'],
                    ['key' => 1, 'value' => 'some'],
                    ['key' => 2, 'value' => 'more'],
                ],
                [
                    0 => 'none',
                    1 => 'some',
                    2 => 'more',
                ],
            ],
            'multidimensional array with non-sequential numbers' => [
                [
                    ['key' => 2, 'value' => 'some'],
                    ['key' => 1, 'value' => 'one'],
                    ['key' => 0, 'value' => 'none'],
                ],
                [
                    2 => 'some',
                    1 => 'one',
                    0 => 'none',
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('keyedAugmentProvider')]
    public function it_augments_keyed($options, $value, $expected)
    {
        $fieldtype = (new Arr)->setField(new Field('test', ['type' => 'arr', 'keys' => $options]));

        $this->assertEquals($expected, $fieldtype->augment($value));
    }

    public static function keyedAugmentProvider()
    {
        return [
            'null' => [
                ['foo' => 'Foo'],
                null,
                null,
            ],
            'associative array options, associative array value' => [
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array options, associative array value' => [
                [
                    ['key' => 'food', 'value' => 'Food'],
                    ['key' => 'drink', 'value' => 'Drink'],
                    ['key' => 'side', 'value' => 'Side'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'associative array options, multidimensional array value' => [
                [
                    'food' => 'Food',
                    'drink' => 'Drink',
                    'side' => 'Side',
                ],
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                    ['key' => 'side', 'value' => 'fries'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array options, multidimensional array value' => [
                [
                    ['key' => 'food', 'value' => 'Food'],
                    ['key' => 'drink', 'value' => 'Drink'],
                    ['key' => 'side', 'value' => 'Side'],
                ],
                [
                    ['key' => 'food', 'value' => 'burger'],
                    ['key' => 'drink', 'value' => 'coke'],
                    ['key' => 'side', 'value' => 'fries'],
                ],
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'side' => 'fries',
                ],
            ],
            'multidimensional array with numbers' => [
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    ['key' => 0, 'value' => 'none'],
                    ['key' => 1, 'value' => 'some'],
                    ['key' => 2, 'value' => 'more'],
                ],
                [
                    0 => 'none',
                    1 => 'some',
                    2 => 'more',
                ],
            ],
            'multidimensional array with non-sequential numbers' => [
                [
                    ['key' => 0, 'value' => 'Zero'],
                    ['key' => 1, 'value' => 'One'],
                    ['key' => 2, 'value' => 'Two'],
                ],
                [
                    ['key' => 2, 'value' => 'some'],
                    ['key' => 1, 'value' => 'one'],
                    ['key' => 0, 'value' => 'none'],
                ],
                [
                    2 => 'some',
                    1 => 'one',
                    0 => 'none',
                ],
            ],
        ];
    }
}
