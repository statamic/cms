<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Tags\Context;
use Tests\TestCase;

class ContextTest extends TestCase
{
    private $value;
    private $antlersValue;
    private $nonAntlersValue;
    private $context;

    public function setUp(): void
    {
        parent::setUp();

        $fieldtype = new class extends \Statamic\Fields\Fieldtype
        {
            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };

        $this->context = (new Context([
            'string' => 'hello',
            'array' => ['one', 'two'],
            'integer' => 7,
            'float' => 123.456,
            'true' => true,
            'false' => false,
            'list' => 'one|two',
            'value' => $this->value = new Value('foo', 'value', $fieldtype),
            'antlersValue' => $this->antlersValue = new Value(
                'parse {{ string }} antlers',
                'antlersValue',
                (clone $fieldtype)->setField(new Field('antlersValue', ['antlers' => true]))
            ),
            'nonAntlersValue' => $this->nonAntlersValue = new Value(
                'dont parse {{ string }} antlers',
                'nonAntlersValue',
                (clone $fieldtype)->setField(new Field('nonAntlersValue', ['antlers' => false]))
            ),
        ]));
    }

    #[Test]
    public function it_gets_all_items()
    {
        $this->assertSame([
            'string' => 'hello',
            'array' => ['one', 'two'],
            'integer' => 7,
            'float' => 123.456,
            'true' => true,
            'false' => false,
            'list' => 'one|two',
            'value' => $this->value,
            'antlersValue' => $this->antlersValue,
            'nonAntlersValue' => $this->nonAntlersValue,
        ], $this->context->all());
    }

    #[Test]
    public function it_gets_a_value()
    {
        $this->assertEquals('hello', $this->context->get('string'));
        $this->assertEquals(['one', 'two'], $this->context->get('array'));
        $this->assertEquals(7, $this->context->get('integer'));
        $this->assertEquals(123.456, $this->context->get('float'));
        $this->assertEquals(true, $this->context->get('true'));
        $this->assertEquals(false, $this->context->get('false'));
        $this->assertEquals('one|two', $this->context->get('list'));

        tap($this->context->get('value'), function ($value) {
            $this->assertInstanceOf(Value::class, $value);
            $this->assertSame('augmented foo', $value->value());
            $this->assertSame('foo', $value->raw());
        });

        tap($this->context->get('antlersValue'), function ($value) {
            $this->assertEquals('parse {{ string }} antlers', $value->raw());
            $this->assertEquals('augmented parse {{ string }} antlers', $value->value());
        });

        tap($this->context->get('nonAntlersValue'), function ($value) {
            $this->assertEquals('dont parse {{ string }} antlers', $value->raw());
            $this->assertEquals('augmented dont parse {{ string }} antlers', $value->value());
        });
    }

    #[Test]
    public function it_gets_raw_values()
    {
        $this->assertSame('hello', $this->context->raw('string'));
        $this->assertSame('foo', $this->context->raw('value'));
        $this->assertSame('parse {{ string }} antlers', $this->context->raw('antlersValue'));
        $this->assertSame('dont parse {{ string }} antlers', $this->context->raw('nonAntlersValue'));
        $this->assertNull($this->context->raw('unknown'));
        $this->assertSame('fallback', $this->context->raw('unknown', 'fallback'));
    }

    #[Test]
    public function it_gets_an_augmented_value()
    {
        $this->assertSame('hello', $this->context->value('string'));
        $this->assertSame('augmented foo', $this->context->value('value'));
        $this->assertSame('augmented parse {{ string }} antlers', $this->context->value('antlersValue'));
        $this->assertSame('augmented dont parse {{ string }} antlers', $this->context->value('nonAntlersValue'));
    }

    #[Test]
    public function unknown_keys_use_a_default_value()
    {
        $this->assertNull($this->context->get('unknown'));
        $this->assertEquals('fallback', $this->context->get('unknown', 'fallback'));
    }

    #[Test]
    public function it_checks_existence()
    {
        $this->assertTrue($this->context->has('string'));
        $this->assertFalse($this->context->has('unknown'));
    }

    #[Test]
    public function it_gets_the_first_parameter_that_exists()
    {
        $this->assertEquals('hello', $this->context->get(['string']));
        $this->assertEquals('hello', $this->context->get(['unknown', 'string']));
        $this->assertNull($this->context->get(['unknown', 'another_unknown']));
        $this->assertEquals('fallback', $this->context->get(['unknown', 'another_unknown'], 'fallback'));
    }

    #[Test]
    public function it_forgets_keys()
    {
        $this->assertEquals('hello', $this->context->get('string'));

        $this->context->forget('string');

        $this->assertNull($this->context->get('string'));
    }

    #[Test]
    public function it_uses_array_access()
    {
        $this->assertEquals('hello', $this->context->get('string'));
        $this->assertEquals('hello', $this->context['string']);
        $this->assertNull($this->context->get('new'));

        $this->context['string'] = 'changed';
        $this->context['new'] = 'value';
        $this->assertEquals('changed', $this->context['string']);
        $this->assertTrue(isset($this->context['new']));
        $this->assertEquals('value', $this->context['new']);

        unset($this->context['new']);
        $this->assertFalse(isset($this->context['new']));
        $this->assertNull($this->context->get('new'));
    }

    #[Test]
    public function it_gets_an_exploded_list()
    {
        $this->assertEquals(['one', 'two'], $this->context->explode('list'));
        $this->assertEquals(['hello'], $this->context->explode('string'));
        $this->assertNull($this->context->explode('unknown'));
        $this->assertEquals('fallback', $this->context->explode('unknown', 'fallback'));
    }

    #[Test]
    public function it_gets_a_boolean()
    {
        $this->assertTrue($this->context->bool('true'));
        $this->assertTrue($this->context->bool('string'));
        $this->assertFalse($this->context->bool('false'));
        $this->assertFalse($this->context->bool('unknown'));
        $this->assertTrue($this->context->bool('unknown', true));
        $this->assertEquals('fallback', $this->context->bool('unknown', 'fallback'));
    }

    #[Test]
    public function it_gets_an_integer()
    {
        $this->assertEquals(7, $this->context->int('integer'));
        $this->assertEquals(0, $this->context->int('string'));
        $this->assertEquals(0, $this->context->int('unknown'));
        $this->assertEquals(3, $this->context->int('unknown', 3));
        $this->assertEquals('fallback', $this->context->int('unknown', 'fallback'));
    }

    #[Test]
    public function it_gets_a_float()
    {
        $this->assertSame(123.456, $this->context->float('float'));
        $this->assertSame(0.0, $this->context->float('string'));
        $this->assertSame(0.0, $this->context->float('unknown'));
        $this->assertSame(3.0, $this->context->float('unknown', 3));
        $this->assertSame('fallback', $this->context->float('unknown', 'fallback'));
    }

    #[Test]
    public function it_is_iterable()
    {
        $expected = [
            'string' => 'hello',
            'array' => ['one', 'two'],
            'integer' => 7,
            'float' => 123.456,
            'true' => true,
            'false' => false,
            'list' => 'one|two',
            'value' => $this->value,
            'antlersValue' => $this->antlersValue,
            'nonAntlersValue' => $this->nonAntlersValue,
        ];

        $actual = [];

        foreach ($this->context as $param => $value) {
            $actual[$param] = $value;
        }

        $this->assertSame($expected, $actual);
    }
}
