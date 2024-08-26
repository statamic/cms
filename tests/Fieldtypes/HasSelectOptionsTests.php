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
        $this->assertEquals($expected, $preloaded['options']);
    }

    public static function optionsProvider()
    {
        return [
            'list' => [
                ['one', 'two', 'three'],
                [
                    ['value' => 'one', 'label' => 'one'],
                    ['value' => 'two', 'label' => 'two'],
                    ['value' => 'three', 'label' => 'three'],
                ],
            ],
            'associative' => [
                ['one' => 'One', 'two' => 'Two', 'three' => 'Three'],
                [
                    ['value' => 'one', 'label' => 'One'],
                    ['value' => 'two', 'label' => 'Two'],
                    ['value' => 'three', 'label' => 'Three'],
                ],
            ],
            'multidimensional' => [
                [
                    ['key' => 'one', 'value' => 'One'],
                    ['key' => 'two', 'value' => 'Two'],
                    ['key' => 'three', 'value' => 'Three'],
                ],
                [
                    ['value' => 'one', 'label' => 'One'],
                    ['value' => 'two', 'label' => 'Two'],
                    ['value' => 'three', 'label' => 'Three'],
                ],
            ],
        ];
    }
}
