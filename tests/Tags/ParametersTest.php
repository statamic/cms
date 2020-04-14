<?php

namespace Tests\Tags;

use Statamic\Facades\Antlers;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Tests\TestCase;

class ParametersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $context = new Context([
            'foo' => 'bar'
        ]);

        $this->params = Parameters::make([
            'string' => 'hello',
            'array' => ['one', 'two'],
            'integer' => 7,
            'float' => 123.456,
            ':evaluated' => 'foo',
            'unevaluated' => 'foo',
            'true' => true,
            'false' => false,
            'truthy' => 'true',
            'falsey' => 'false',
            'list' => 'one|two',
        ], $context);
    }

    /** @test */
    function it_gets_all_parameters()
    {
        $this->assertSame([
            'string' => 'hello',
            'array' => ['one', 'two'],
            'integer' => 7,
            'float' => 123.456,
            'evaluated' => 'bar',
            'unevaluated' => 'foo',
            'true' => true,
            'false' => false,
            'truthy' => true,
            'falsey' => false,
            'list' => 'one|two',
        ], $this->params->all());
    }

    /** @test */
    function it_gets_a_parameter()
    {
        $this->assertEquals('hello', $this->params->get('string'));
        $this->assertEquals(['one', 'two'], $this->params->get('array'));
        $this->assertEquals(7, $this->params->get('integer'));
        $this->assertEquals(123.456, $this->params->get('float'));
        $this->assertEquals('bar', $this->params->get('evaluated'));
        $this->assertEquals('foo', $this->params->get('unevaluated'));
        $this->assertEquals(true, $this->params->get('true'));
        $this->assertEquals(false, $this->params->get('false'));
        $this->assertEquals(true, $this->params->get('truthy'));
        $this->assertEquals(false, $this->params->get('falsey'));
        $this->assertEquals('one|two', $this->params->get('list'));
    }

    /** @test */
    function it_gets_a_value_objects_value()
    {
        $fieldtype = $this->partialMock(Fieldtype::class);
        $fieldtype->shouldReceive('augment')->with('the raw value')->andReturn('the augmented value');
        $value = new Value('the raw value', 'test', $fieldtype);

        $params = Parameters::make(['test' => $value], new Context);

        $this->assertIsString($params->get('test'));
        $this->assertSame('the augmented value', $params->get('test'));
    }

    /** @test */
    function unknown_keys_use_a_default_value()
    {
        $this->assertNull($this->params->get('unknown'));
        $this->assertEquals('fallback', $this->params->get('unknown', 'fallback'));
    }

    /** @test */
    function it_checks_existence()
    {
        $this->assertTrue($this->params->has('string'));
        $this->assertFalse($this->params->has('unknown'));
    }

    /** @test */
    function it_gets_the_first_parameter_that_exists()
    {
        $this->assertEquals('hello', $this->params->get(['string']));
        $this->assertEquals('hello', $this->params->get(['unknown', 'string']));
        $this->assertNull($this->params->get(['unknown', 'another_unknown']));
        $this->assertEquals('fallback', $this->params->get(['unknown', 'another_unknown'], 'fallback'));
    }

    /** @test */
    function it_forgets_keys()
    {
        $this->assertEquals('hello', $this->params->get('string'));

        $this->params->forget('string');

        $this->assertNull($this->params->get('string'));
    }

    /** @test */
    function it_uses_array_access()
    {
        $this->assertEquals('hello', $this->params->get('string'));
        $this->assertEquals('hello', $this->params['string']);
        $this->assertNull($this->params->get('new'));

        $this->params['string'] = 'changed';
        $this->params['new'] = 'value';
        $this->assertEquals('changed', $this->params['string']);
        $this->assertTrue(isset($this->params['new']));
        $this->assertEquals('value', $this->params['new']);

        unset($this->params['new']);
        $this->assertFalse(isset($this->params['new']));
        $this->assertNull($this->params->get('new'));
    }

    /** @test */
    function it_gets_an_exploded_list()
    {
        $this->assertEquals(['one', 'two'], $this->params->explode('list'));
        $this->assertEquals(['hello'], $this->params->explode('string'));
        $this->assertNull($this->params->explode('unknown'));
        $this->assertEquals('fallback', $this->params->explode('unknown', 'fallback'));
    }

    /** @test */
    function it_gets_a_boolean()
    {
        $this->assertTrue($this->params->bool('true'));
        $this->assertTrue($this->params->bool('truthy'));
        $this->assertTrue($this->params->bool('string'));
        $this->assertFalse($this->params->bool('false'));
        $this->assertFalse($this->params->bool('falsey'));
        $this->assertFalse($this->params->bool('unknown'));
        $this->assertTrue($this->params->bool('unknown', true));
        $this->assertEquals('fallback', $this->params->bool('unknown', 'fallback'));
    }

    /** @test */
    function it_gets_an_integer()
    {
        $this->assertEquals(7, $this->params->int('integer'));
        $this->assertEquals(0, $this->params->int('string'));
        $this->assertEquals(0, $this->params->int('unknown'));
        $this->assertEquals(3, $this->params->int('unknown', 3));
        $this->assertEquals('fallback', $this->params->int('unknown', 'fallback'));
    }

    /** @test */
    function it_gets_a_float()
    {
        $this->assertSame(123.456, $this->params->float('float'));
        $this->assertSame(0.0, $this->params->float('string'));
        $this->assertSame(0.0, $this->params->float('unknown'));
        $this->assertSame(3.0, $this->params->float('unknown', 3));
        $this->assertSame('fallback', $this->params->float('unknown', 'fallback'));
    }

    /** @test */
    function it_is_iterable()
    {
        $expected = [
            'string' => 'hello',
            'array' => ['one', 'two'],
            'integer' => 7,
            'float' => 123.456,
            'evaluated' => 'bar',
            'unevaluated' => 'foo',
            'true' => true,
            'false' => false,
            'truthy' => true,
            'falsey' => false,
            'list' => 'one|two',
        ];

        $actual = [];

        foreach ($this->params as $param => $value) {
            $actual[$param] = $value;
        }

        $this->assertSame($expected, $actual);
    }
}
