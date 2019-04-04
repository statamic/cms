<?php

namespace Tests\Data;

use Mockery;
use Tests\TestCase;
use Statamic\Data\DataRepository;

class DataRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->data = (new DataRepository);
    }

    /** @test */
    function it_gets_the_repository_key_and_the_id()
    {
        $this->assertEquals(['entry', '123'], $this->data->splitReference('entry::123'));
        $this->assertEquals([null, '123'], $this->data->splitReference('123'));
        $this->assertEquals(['asset', 'main::foo/bar'], $this->data->splitReference('asset::main::foo/bar'));
    }

    /** @test */
    function it_proxies_find_to_a_repository()
    {
        $this->app->instance('FooRepository', Mockery::mock('FooRepository', function ($m) {
            $m->shouldReceive('find')->once()->with('123')->andReturn('test');
        }));

        $this->data->setRepository('foo', 'FooRepository');

        $this->assertEquals('test', $this->data->find('foo::123'));
    }

    /** @test */
    function when_a_repository_key_isnt_provided_it_will_loop_through_repositories()
    {
        $this->app->instance('FooRepository', Mockery::mock('FooRepository', function ($m) {
            $m->shouldReceive('find')->once()->with('123')->andReturnNull();
        }));
        $this->app->instance('BarRepository', Mockery::mock('BarRepository', function ($m) {
            $m->shouldReceive('find')->once()->with('123')->andReturn('test');
        }));
        $this->app->instance('BazRepository', Mockery::mock('BazRepository', function ($m) {
            $m->shouldReceive('find')->never();
        }));

        $this->data
            ->setRepository('foo', 'FooRepository')
            ->setRepository('bar', 'BarRepository')
            ->setRepository('baz', 'BazRepository');

        $this->assertEquals('test', $this->data->find('123'));
    }
}