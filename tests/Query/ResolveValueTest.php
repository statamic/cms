<?php

namespace Tests\Query;

use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Query\ResolveValue;
use Tests\TestCase;

class ResolveValueTest extends TestCase
{
    /**
     * @test
     * @dataProvider resolvesValueProvider
     **/
    public function it_resolves_values($item, $expected)
    {
        $value = (new ResolveValue)($item, 'the_foo_field');

        $this->assertEquals($expected, $value);
    }

    public function resolvesValueProvider()
    {
        $data = ['the_foo_field' => 'getfoo'];
        $values = ['the_foo_field' => 'valuefoo'];

        return [
            'get' => [new ContainsData($data), 'getfoo'],
            'value' => [new ContainsValues($data, $values), 'valuefoo'],
            'method' => [new ContainsMethod($data, $values), 'theFooField method'],
        ];
    }

    /**
     * @test
     * @dataProvider resolvesNestedJsonValueProvider
     **/
    public function it_resolves_nested_json_values($item, $expected)
    {
        $value = (new ResolveValue)($item, 'the_nested_field->the_foo_field');

        $this->assertEquals($expected, $value);
    }

    public function resolvesNestedJsonValueProvider()
    {
        $data = ['the_nested_field' => ['the_foo_field' => 'getfoo']];
        $values = ['the_nested_field' => ['the_foo_field' => 'valuefoo']];

        return [
            'get' => [new ContainsData($data), 'getfoo'],
            'value' => [new ContainsValues($data, $values), 'valuefoo'],
            'method' => [new ContainsMethod($data, $values), 'valuefoo'], // because there's no "theNestedField" method.
        ];
    }

    /**
     * @test
     * @dataProvider resolvesMissingNestedJsonValueProvider
     **/
    public function it_resolves_missing_nested_json_values_to_null($item)
    {
        $value = (new ResolveValue)($item, 'the_nested_field->missing');

        $this->assertNull($value);
    }

    public function resolvesMissingNestedJsonValueProvider()
    {
        $data = ['the_nested_field' => ['the_foo_field' => 'getfoo']];
        $values = ['the_nested_field' => ['the_foo_field' => 'valuefoo']];

        return [
            'get' => [new ContainsData($data)],
            'value' => [new ContainsValues($data, $values)],
            'method' => [new ContainsMethod($data, $values)],
        ];
    }

    /**
     * @test
     * @dataProvider resolvesScalarNestedJsonValueProvider
     **/
    public function it_resolves_scalar_nested_json_values_to_null($item)
    {
        // When you try to query a json value like 'foo->bar', 'foo' should be an array, but since
        // it's a scalar we'll just return null.

        $value = (new ResolveValue)($item, 'the_foo_field->test');

        $this->assertNull($value);
    }

    public function resolvesScalarNestedJsonValueProvider()
    {
        $data = ['the_foo_field' => 'getfoo'];
        $values = ['the_foo_field' => 'valuefoo'];

        return [
            'get' => [new ContainsData($data)],
            'value' => [new ContainsValues($data, $values)],
            'method' => [new ContainsMethod($data, $values)],
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
