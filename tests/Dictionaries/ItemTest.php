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
}
