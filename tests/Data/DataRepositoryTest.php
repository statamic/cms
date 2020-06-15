<?php

namespace Tests\Data;

use Mockery;
use Statamic\Data\DataRepository;
use Tests\TestCase;

class DataRepositoryTest extends TestCase
{
    // Mocking method_exists, courtesy of https://stackoverflow.com/a/37928161
    public static $functions;

    public function setUp(): void
    {
        parent::setUp();

        static::$functions = Mockery::mock();

        $this->data = (new DataRepository);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        static::$functions = null;
    }

    /** @test */
    public function it_gets_the_repository_key_and_the_id()
    {
        $this->assertEquals(['entry', '123'], $this->data->splitReference('entry::123'));
        $this->assertEquals([null, '123'], $this->data->splitReference('123'));
        $this->assertEquals(['asset', 'main::foo/bar'], $this->data->splitReference('asset::main::foo/bar'));
    }

    /** @test */
    public function it_proxies_find_to_a_repository()
    {
        $this->app->instance('FooRepository', Mockery::mock('FooRepository', function ($m) {
            $m->shouldReceive('find')->once()->with('123')->andReturn('test');
        }));

        $this->data->setRepository('foo', 'FooRepository');

        $this->assertEquals('test', $this->data->find('foo::123'));
    }

    /** @test */
    public function when_a_repository_key_isnt_provided_it_will_loop_through_repositories()
    {
        $this->app->instance('FooRepository', Mockery::mock('FooRepository', function ($m) {
            self::$functions->shouldReceive('method_exists')->with('FooRepository', 'find')->once()->andReturnTrue();
            $m->shouldReceive('find')->once()->with('123')->andReturnNull();
        }));
        $this->app->instance('WithoutFindMethodRepository', Mockery::mock('WithoutFindMethodRepository', function ($m) {
            self::$functions->shouldReceive('method_exists')->with('WithoutFindMethodRepository', 'find')->once()->andReturnFalse();
            $m->shouldReceive('find')->never();
        }));
        $this->app->instance('BarRepository', Mockery::mock('BarRepository', function ($m) {
            self::$functions->shouldReceive('method_exists')->with('BarRepository', 'find')->once()->andReturnTrue();
            $m->shouldReceive('find')->once()->with('123')->andReturn('test');
        }));
        $this->app->instance('BazRepository', Mockery::mock('BazRepository', function ($m) {
            self::$functions->shouldReceive('method_exists')->never();
            $m->shouldReceive('find')->never();
        }));

        $this->data
            ->setRepository('foo', 'FooRepository')
            ->setRepository('withoutFind', 'WithoutFindMethodRepository')
            ->setRepository('bar', 'BarRepository')
            ->setRepository('baz', 'BazRepository');

        $this->assertEquals('test', $this->data->find('123'));
    }
}

namespace Statamic\Data;

function method_exists($object, $method)
{
    // Once this file is loaded, Statamic\Data\method_exists will exist and affect other
    // tests being run. In tearDown, we'll remove it so it can fall back to the global.
    $functions = \Tests\Data\DataRepositoryTest::$functions;

    return $functions
        ? $functions->method_exists($object, $method)
        : \method_exists($object, $method);
}
