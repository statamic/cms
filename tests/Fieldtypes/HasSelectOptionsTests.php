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

        // Use json_encode data to strictly check all data types as well, which assertEquals ignores.
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($preloaded['options']));
    }

    #[Test]
    #[DataProvider('optionsProvider')]
    public function it_augments_single_values($options, $expected)
    {
        $fieldType = $this->field(['options' => $options]);

        if (! in_array(MultipleLabeledValueTests::class, class_uses_recursive(self::class))) {
            $this->assertSame($fieldType->augment(50)?->value(), 50);
            $this->assertSame($fieldType->augment('50')?->value(), 50);
            $this->assertSame($fieldType->augment(100)?->value(), 100);
            $this->assertSame($fieldType->augment('100')?->value(), 100);
            $this->assertSame($fieldType->augment('one')?->value(), 'one');
        } else {
            $this->expectNotToPerformAssertions();
        }
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
}
