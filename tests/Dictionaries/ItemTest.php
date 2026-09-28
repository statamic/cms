<?php

namespace Tests\Dictionaries;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Item;
use Tests\TestCase;

class ItemTest extends TestCase
{
    #[Test]
    public function it_gets_value_label_and_data()
    {
        $item = new Item('apple', '🍎 Apple', [
            'label' => 'Apple', // Ensures the label argument takes precedence.
            'color' => 'red',
            'emoji' => '🍎',
        ]);

        $this->assertEquals('apple', $item->value());
        $this->assertEquals('🍎 Apple', $item->label());
        $this->assertEquals(['color' => 'red', 'emoji' => '🍎'], $item->data());
        $this->assertEquals([
            'key' => 'apple',
            'value' => 'apple',
            'color' => 'red',
            'emoji' => '🍎',
            'label' => '🍎 Apple',
        ], $item->toArray());
    }

    #[Test]
    public function it_gets_the_icon()
    {
        $item = new Item('apple', 'Apple', [
            'icon' => 'apple',
            'color' => 'red',
        ]);

        $this->assertEquals('apple', $item->icon());
        $this->assertEquals(['color' => 'red'], $item->data());
        $this->assertEquals([
            'key' => 'apple',
            'value' => 'apple',
            'icon' => 'apple',
            'color' => 'red',
            'label' => 'Apple',
        ], $item->toArray());
    }

    #[Test]
    public function icon_is_null_when_not_set()
    {
        $item = new Item('apple', 'Apple', ['color' => 'red']);

        $this->assertNull($item->icon());
    }
}
