<?php

namespace Tests\Fields;

use Illuminate\Contracts\Support\Arrayable;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\ArrayableString;
use Tests\TestCase;

class ArrayableStringTest extends TestCase
{
    #[Test]
    public function it_gets_the_value_and_extra()
    {
        $obj = new ArrayableString('foo', ['label' => 'Foo']);

        $this->assertEquals('foo', $obj->value());
        $this->assertEquals(['label' => 'Foo'], $obj->extra());
    }

    #[Test]
    public function it_converts_to_a_string()
    {
        $this->assertSame('world', (new ArrayableString('world'))->__toString());
        $this->assertSame('', (new ArrayableString(null))->__toString());
        $this->assertSame('4', (new ArrayableString(4))->__toString());
    }

    #[Test]
    public function it_converts_to_an_array()
    {
        $val = new ArrayableString('foo', ['one' => 'a', 'two' => 'b']);

        $this->assertInstanceOf(Arrayable::class, $val);
        $this->assertEquals([
            'value' => 'foo',
            'one' => 'a',
            'two' => 'b',
        ], $val->toArray());
    }

    #[Test]
    public function it_converts_to_json()
    {
        $val = new ArrayableString('foo', ['one' => 'a', 'two' => 'b']);

        $this->assertSame(json_encode($val->toArray()), json_encode($val));
    }

    #[Test]
    public function it_converts_to_bool()
    {
        $this->assertTrue((new ArrayableString('world'))->toBool());
        $this->assertFalse((new ArrayableString(null))->toBool());
        $this->assertTrue((new ArrayableString(4))->toBool());
        $this->assertFalse((new ArrayableString(''))->toBool());
    }

    #[Test]
    public function it_uses_array_access()
    {
        $val = new ArrayableString('foo', ['one' => 'a', 'two' => 'b']);

        $this->assertTrue(isset($val['one']));
        $this->assertFalse(isset($val['three']));
        $this->assertEquals('a', $val['one']);
        $this->assertEquals('nope', $val['three'] ?? 'nope');
    }
}
