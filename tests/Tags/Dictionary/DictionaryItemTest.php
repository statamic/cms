<?php

namespace Tests\Tags\Dictionary;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Tags\Dictionary\DictionaryItem;
use Tests\TestCase;

class DictionaryItemTest extends TestCase
{
    #[Test]
    public function it_uses_array_access_with_array()
    {
        $val = new DictionaryItem([
            'a' => 'alfa',
            'b' => 'bravo',
        ]);

        $this->assertTrue(isset($val['a']));
        $this->assertFalse(isset($val['c']));
        $this->assertEquals('alfa', $val['a'] ?? 'nope');
        $this->assertEquals('nope', $val['c'] ?? 'nope');
    }

    #[Test]
    public function it_can_proxy_property_access_to_value()
    {
        $value = new DictionaryItem([
            'a' => 'alfa',
        ]);

        $this->assertEquals('alfa', $value->a);
        $this->assertEquals('nope', $value->b ?? 'nope');
    }
}
