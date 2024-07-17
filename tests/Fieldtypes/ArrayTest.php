<?php

namespace Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Tests\TestCase;

class ArrayTest extends TestCase
{
    #[Test]
    public function it_preprocesses_with_legacy_format()
    {
        $field = new Field('test', ['type' => 'array']);

        $field->setValue([
            'foo' => 'bar',
            'baz' => 'qux',
            'quux' => 'quuz',
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'qux',
            'quux' => 'quuz',
        ], $field->preProcess()->value());
    }

    #[Test]
    public function it_preprocesses()
    {
        $field = new Field('test', ['type' => 'array']);

        $field->setValue([
            ['key' => 'foo', 'value' => 'bar'],
            ['key' => 'baz', 'value' => 'qux'],
            ['key' => 'quux', 'value' => 'quuz'],
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'qux',
            'quux' => 'quuz',
        ], $field->preProcess()->value());
    }

    #[Test]
    public function it_processes()
    {
        $field = new Field('test', ['type' => 'array']);

        $field->setValue([
            'foo' => 'bar',
            'baz' => 'qux',
            'quux' => 'quuz',
        ]);

        $this->assertEquals([
            ['key' => 'foo', 'value' => 'bar'],
            ['key' => 'baz', 'value' => 'qux'],
            ['key' => 'quux', 'value' => 'quuz'],
        ], $field->process()->value());
    }
}
