<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Tests\TestCase;

class ParametersTest extends TestCase
{
    private $params;
    private $value;
    private $antlersValue;
    private $nonAntlersValue;

    public function setUp(): void
    {
        parent::setUp();

        $context = new Context([
            'foo' => 'bar',
            'nested' => [
                'foo' => 'bar',
            ],
        ]);

        $fieldtype = new class extends \Statamic\Fields\Fieldtype
        {
            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };

        $this->params = Parameters::make([
            'string' => 'hello',
            'array' => ['one', 'two'],
            'zero' => 0,
            'integer' => 7,
            'float' => 123.456,
            ':evaluated' => 'foo',
            'unevaluated' => 'foo',
            ':evaluatednested' => 'nested:foo',
            'unevaluatednested' => 'nested:foo',
            ':notInContext' => 'not_in_context',
            'true' => true,
            'false' => false,
            'truthy' => 'true',
            'falsey' => 'false',
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
        ], $context);
    }

    #[Test]
    public function it_gets_all_parameters()
    {
        $this->assertSame([
            'string' => 'hello',
            'array' => ['one', 'two'],
            'zero' => 0,
            'integer' => 7,
            'float' => 123.456,
            'evaluated' => 'bar',
            'unevaluated' => 'foo',
            'evaluatednested' => 'bar',
            'unevaluatednested' => 'nested:foo',
            'notInContext' => null,
            'true' => true,
            'false' => false,
            'truthy' => true,
            'falsey' => false,
            'list' => 'one|two',
            'value' => 'augmented foo',
            'antlersValue' => 'augmented parse {{ string }} antlers',
            'nonAntlersValue' => 'augmented dont parse {{ string }} antlers',
        ], $this->params->all());
    }

    #[Test]
    public function it_gets_a_parameter()
    {
        $this->assertEquals('hello', $this->params->get('string'));
        $this->assertEquals(['one', 'two'], $this->params->get('array'));
        $this->assertEquals(7, $this->params->get('integer'));
        $this->assertEquals(123.456, $this->params->get('float'));
        $this->assertEquals('bar', $this->params->get('evaluated'));
        $this->assertEquals('foo', $this->params->get('unevaluated'));
        $this->assertEquals(null, $this->params->get('notInContext'));
        $this->assertEquals(true, $this->params->get('true'));
        $this->assertEquals(false, $this->params->get('false'));
        $this->assertEquals(true, $this->params->get('truthy'));
        $this->assertEquals(false, $this->params->get('falsey'));
        $this->assertEquals('one|two', $this->params->get('list'));
        $this->assertSame('augmented foo', $this->params->get('value'));
        $this->assertSame('augmented parse {{ string }} antlers', $this->params->get('antlersValue'));
        $this->assertSame('augmented dont parse {{ string }} antlers', $this->params->get('nonAntlersValue'));
    }

    #[Test]
    public function unknown_keys_use_a_default_value()
    {
        $this->assertNull($this->params->get('unknown'));
        $this->assertEquals('fallback', $this->params->get('unknown', 'fallback'));
    }

    #[Test]
    public function it_checks_existence()
    {
        $this->assertTrue($this->params->has('string'));
        $this->assertFalse($this->params->has('unknown'));

        $this->assertTrue($this->params->hasAny(['string', 'unknown']));
        $this->assertFalse($this->params->hasAny(['unknown', 'another_unknown']));
    }

    #[Test]
    public function it_gets_the_first_parameter_that_exists()
    {
        $this->assertEquals('hello', $this->params->get(['string']));
        $this->assertEquals('hello', $this->params->get(['unknown', 'string']));
        $this->assertNull($this->params->get(['unknown', 'another_unknown']));
        $this->assertEquals('fallback', $this->params->get(['unknown', 'another_unknown'], 'fallback'));
    }

    #[Test]
    public function it_forgets_keys()
    {
        $this->assertEquals('hello', $this->params->get('string'));

        $this->params->forget('string');

        $this->assertNull($this->params->get('string'));
    }

    #[Test]
    public function it_uses_array_access()
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

    #[Test]
    public function it_gets_an_exploded_list()
    {
        $this->assertEquals(['one', 'two'], $this->params->explode('list'));
        $this->assertEquals(['hello'], $this->params->explode('string'));
        $this->assertNull($this->params->explode('unknown'));
        $this->assertEquals('fallback', $this->params->explode('unknown', 'fallback'));
    }

    #[Test]
    public function it_gets_a_boolean()
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

    #[Test]
    public function it_gets_an_integer()
    {
        $this->assertEquals(7, $this->params->int('integer'));
        $this->assertEquals(0, $this->params->int('string'));
        $this->assertEquals(0, $this->params->int('unknown'));
        $this->assertEquals(3, $this->params->int('unknown', 3));
        $this->assertEquals(0, $this->params->int('zero'));
        $this->assertEquals(0, $this->params->int('zero', 1));
        $this->assertEquals('fallback', $this->params->int('unknown', 'fallback'));
    }

    #[Test]
    public function it_gets_a_float()
    {
        $this->assertSame(123.456, $this->params->float('float'));
        $this->assertSame(0.0, $this->params->float('string'));
        $this->assertSame(0.0, $this->params->float('unknown'));
        $this->assertSame(3.0, $this->params->float('unknown', 3));
        $this->assertSame('fallback', $this->params->float('unknown', 'fallback'));
    }

    /**
     * @see https://github.com/statamic/cms/issues/3248
     */
    #[Test]
    public function it_gets_nested_values()
    {
        $augmentable = new class implements \Statamic\Contracts\Data\Augmentable
        {
            use \Statamic\Data\HasAugmentedData;

            public function augmentedArrayData()
            {
                return [
                    'foo' => 'a',
                ];
            }
        };

        $context = new Context([
            'array' => ['foo' => 'b'],
            'object' => $augmentable,
        ]);

        $params = Parameters::make([
            ':arr' => 'array:foo',
            ':obj' => 'object:foo',
        ], $context);

        $this->assertSame('b', $params->get('arr'));
        $this->assertSame('a', $params->get('obj'));
    }

    #[Test]
    public function it_can_use_modifiers()
    {
        $context = new Context(['foo' => 'bar']);

        $params = Parameters::make([
            ':evaluated' => 'foo|upper',
            ':double_quotes' => '"double"|upper|reverse',
            ':single_quotes' => "'single'|upper|reverse",
            ':double_quotes_with_spaces' => '"double" | upper | reverse',
            ':single_quotes_with_spaces' => "'single' | upper | reverse",
        ], $context);

        $this->assertSame('BAR', $params->get('evaluated'));
        $this->assertSame('ELBUOD', $params->get('double_quotes'));
        $this->assertSame('ELGNIS', $params->get('single_quotes'));
        $this->assertSame('ELBUOD', $params->get('double_quotes_with_spaces'));
        $this->assertSame('ELGNIS', $params->get('single_quotes_with_spaces'));
    }

    #[Test]
    public function it_is_iterable()
    {
        $expected = [
            'string' => 'hello',
            'array' => ['one', 'two'],
            'zero' => 0,
            'integer' => 7,
            'float' => 123.456,
            'evaluated' => 'bar',
            'unevaluated' => 'foo',
            'evaluatednested' => 'bar',
            'unevaluatednested' => 'nested:foo',
            'notInContext' => null,
            'true' => true,
            'false' => false,
            'truthy' => true,
            'falsey' => false,
            'list' => 'one|two',
            'value' => 'augmented foo',
            'antlersValue' => 'augmented parse {{ string }} antlers',
            'nonAntlersValue' => 'augmented dont parse {{ string }} antlers',
        ];

        $actual = [];

        foreach ($this->params as $param => $value) {
            $actual[$param] = $value;
        }

        $this->assertSame($expected, $actual);
    }
}
