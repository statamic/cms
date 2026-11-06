<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

trait HasSelectOptionsTests
{
    #[Test]
    #[DataProvider('optionsProvider')]
    public function it_preloads_options($options, $expected)
    {
        $field = $this->field(['options' => $options]);

        $this->assertArrayHasKey('options', $preloaded = $field->preload());
        $this->assertSame($expected, $preloaded['options']);
    }

    public static function optionsProvider()
    {
        return [
            'list' => [
                ['one', 'two', 'three', 50, '100'],
                [
                    ['value' => 'one', 'label' => 'one'],
                    ['value' => 'two', 'label' => 'two'],
                    ['value' => 'three', 'label' => 'three'],
                    ['value' => 50, 'label' => 50],
                    ['value' => '100', 'label' => '100'],
                ],
            ],
            'associative' => [
                ['one' => 'One', 'two' => 'Two', 'three' => 'Three', 50 => '50', '100' => 100],
                [
                    ['value' => 'one', 'label' => 'One'],
                    ['value' => 'two', 'label' => 'Two'],
                    ['value' => 'three', 'label' => 'Three'],
                    ['value' => 50, 'label' => '50'],
                    ['value' => 100, 'label' => 100],
                ],
            ],
            'multidimensional' => [
                [
                    ['key' => 'one', 'value' => 'One'],
                    ['key' => 'two', 'value' => 'Two'],
                    ['key' => 'three', 'value' => 'Three'],
                    ['key' => 50, 'value' => 50],
                    ['key' => '100', 'value' => 100],
                ],
                [
                    ['value' => 'one', 'label' => 'One'],
                    ['value' => 'two', 'label' => 'Two'],
                    ['value' => 'three', 'label' => 'Three'],
                    ['value' => 50, 'label' => 50],
                    ['value' => '100', 'label' => 100],
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('preProcessIndexProvider')]
    public function it_preprocesses_index_values($options, $value, $expected)
    {
        $field = $this->field(['options' => $options]);

        $this->assertSame($expected, $field->preProcessIndex($value));
    }

    public static function preProcessIndexProvider()
    {
        return [
            'list' => [
                ['one', 'two', 'three'],
                'two',
                ['two'],
            ],
            'associative with labels' => [
                ['one' => 'One', 'two' => 'Two', 'three' => 'Three'],
                'two',
                ['Two'],
            ],
            'associative without labels' => [
                ['one' => null, 'two' => null, 'three' => null],
                'two',
                ['two'],
            ],
            'multidimensional with labels' => [
                [
                    ['key' => 'one', 'value' => 'One'],
                    ['key' => 'two', 'value' => 'Two'],
                    ['key' => 'three', 'value' => 'Three'],
                ],
                'two',
                ['Two'],
            ],
            'multidimensional without labels' => [
                [
                    ['key' => 'one', 'value' => null],
                    ['key' => 'two', 'value' => null],
                    ['key' => 'three', 'value' => null],
                ],
                'two',
                ['two'],
            ],
            'numeric keys with string value' => [
                [
                    ['key' => 926450000, 'value' => 'One'],
                    ['key' => 926450001, 'value' => 'Two'],
                ],
                '926450001',
                ['Two'],
            ],
            'numeric keys with differently formatted string value' => [
                [
                    ['key' => 1, 'value' => 'One'],
                ],
                '01',
                ['01'],
            ],
            'numeric and string keys with string value' => [
                [
                    ['key' => 1, 'value' => 'One'],
                    ['key' => '1', 'value' => 'String One'],
                ],
                '1',
                ['String One'],
            ],
        ];
    }
}
