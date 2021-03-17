<?php

namespace Tests\Fields;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Fields\ArrayableString;
use Tests\TestCase;

class ArrayableStringTest extends TestCase
{
    /** @test */
    public function it_gets_the_value_and_extra()
    {
        $obj = new ArrayableString('foo', ['label' => 'Foo']);

        $this->assertEquals('foo', $obj->value());
        $this->assertEquals(['label' => 'Foo'], $obj->extra());
    }

    /** @test */
    public function it_converts_to_a_string()
    {
        $this->assertSame('world', (new ArrayableString('world'))->__toString());
        $this->assertSame('', (new ArrayableString(null))->__toString());
        $this->assertSame('4', (new ArrayableString(4))->__toString());
    }

    /** @test */
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

    /** @test */
    public function it_converts_to_json()
    {
        $val = new ArrayableString('foo', ['one' => 'a', 'two' => 'b']);

        $this->assertSame(json_encode($val->toArray()), json_encode($val));
    }
}
