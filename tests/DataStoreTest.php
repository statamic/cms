<?php namespace Tests;

use Statamic\DataStore;

class DataStoreTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Statamic\DataStore */
    private $store;

    public function setUp(): void
    {
        parent::setUp();

        $this->store = new DataStore;

        $this->store->mergeInto('one', ['foo' => 'bar']);
    }

    public function testScopeIsCreated()
    {
        $this->store->mergeInto('foo', ['bar' => 'baz']);

        $this->assertEquals(['bar' => 'baz'], $this->store->getScope('foo'));
    }

    public function testCheckScopeExists()
    {
        $this->assertTrue($this->store->isScope('one'));
        $this->assertFalse($this->store->isScope('two'));
    }

    public function testMergeIntoDefaultScope()
    {
        $this->store->merge(['baz' => 'qux']);

        $this->assertEquals(['baz' => 'qux'], $this->store->getScope('cascade'));
    }

    public function testMergeScopes()
    {
        $this->store->merge(['baz' => 'qux'], 'one');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $this->store->getScope('one'));
    }

    public function testMergeIntoScope()
    {
        $this->store->mergeInto('one', ['baz' => 'qux']);

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $this->store->getScope('one'));
    }

    public function testGetScope()
    {
        $this->assertEquals(['foo' => 'bar'], $this->store->getScope('one'));
    }

    public function testGetAllVariables()
    {
        $this->store->mergeInto('two', ['one' => 'uno', 'two' => 'dos']);
        $this->store->mergeInto('three', ['one' => 'une', 'two' => 'deux']);

        $expected = [
            'one' => ['foo' => 'bar'],
            'two' => ['one' => 'uno', 'two' => 'dos'],
            'three' => ['one' => 'une', 'two' => 'deux']
        ];

        $this->assertEquals($expected, $this->store->getAll());
    }

    public function testSetScopeVariable()
    {
        $this->store->merge(['foo' => 'qux'], 'one');

        $foo = array_get($this->store->getScope('one'), 'foo');
        $this->assertEquals('qux', $foo);
    }

    public function testMergesWithDotNotation()
    {
        $this->store->mergeInto('one.two', ['hello' => 'world']);

        $this->assertEquals(['hello' => 'world'], $this->store->getScope('one.two'));
    }
}
