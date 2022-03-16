<?php

namespace Tests\Query;

use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Query\QueryableValue;
use Statamic\Query\ResolveValue;
use Tests\TestCase;

class ResolveValueTest extends TestCase
{
    /**
     * @test
     * @dataProvider resolvesValueProvider
     **/
    public function it_resolves_values($item, $field, $expected)
    {
        $value = (new ResolveValue)($item, $field);

        $this->assertEquals($expected, $value);
    }

    public function resolvesValueProvider()
    {
        $data = [
            'the_foo_field' => 'getfoo',
            'the_nested_field' => ['the_foo_field' => 'getfoo'],
        ];

        $values = [
            'the_foo_field' => 'valuefoo',
            'the_nested_field' => ['the_foo_field' => 'valuefoo'],
        ];

        $dataItem = new ContainsData($data);
        $valueItem = new ContainsValues($data, $values);
        $methodItem = new ContainsMethod($data, $values);

        return [
            'get' => [$dataItem, 'the_foo_field', 'getfoo'],
            'value' => [$valueItem, 'the_foo_field', 'valuefoo'],
            'method' => [$methodItem, 'the_foo_field', 'theFooField method'],

            'nested get' => [$dataItem, 'the_nested_field->the_foo_field', 'getfoo'],
            'nested value' => [$valueItem, 'the_nested_field->the_foo_field', 'valuefoo'],
            'nested method' => [$methodItem, 'the_nested_field->the_foo_field', 'valuefoo'], // because there's no "theNestedField" method.

            'missing nested get' => [$dataItem, 'the_nested_field->missing', null],
            'missing nested value' => [$valueItem, 'the_nested_field->missing', null],
            'missing nested method' => [$methodItem, 'the_nested_field->missing', null],

            'scalar nested get' => [$dataItem, 'the_foo_field->test', null],
            'scalar nested value' => [$valueItem, 'the_foo_field->test', null],
            'scalar nested method' => [$methodItem, 'the_foo_field->test', null],
        ];
    }

    /**
     * @test
     * @dataProvider delegatesToClassProvider
     **/
    public function it_delegates_resolving_to_the_queryable_class($field, $expected)
    {
        $item = new ItemThatContainsQueryableValues([
            'foo' => 'bar',
            'nested' => ['baz' => 'qux'],
        ]);

        $value = (new ResolveValue)($item, $field);

        $this->assertEquals($expected, $value);
    }

    public function delegatesToClassProvider()
    {
        return [
            'standard' => ['foo', 'bar'],
            'nested' => ['nested->baz', 'qux'],
            'nested missing' => ['nested->missing', null],
            'nested string' => ['foo->bar', null],
        ];
    }

    /** @test */
    public function self_resolving_values_will_resolve_themselves()
    {
        $item = new ContainsData([
            'queryable' => new ResolvedQueryableValue('test'),
        ]);

        $value = (new ResolveValue)($item, 'queryable');

        $this->assertEquals('test', $value);
    }
}

class ContainsData
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function get($field)
    {
        return $this->data[$field];
    }
}

class ContainsValues extends ContainsData
{
    protected $values;

    public function __construct($data, $values)
    {
        parent::__construct($data);
        $this->values = $values;
    }

    public function value($field)
    {
        return $this->values[$field];
    }
}

class ContainsMethod extends ContainsValues
{
    public function theFooField()
    {
        return 'theFooField method';
    }
}

class ItemThatContainsQueryableValues implements ContainsQueryableValues
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getQueryableValue(string $field)
    {
        return $this->data[$field];
    }
}

class ResolvedQueryableValue implements QueryableValue
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function toQueryableValue()
    {
        return $this->value;
    }
}
