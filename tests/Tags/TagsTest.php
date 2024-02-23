<?php

namespace Tests\Tags;

use Statamic\Facades\Antlers;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Statamic\Tags\Tags;
use Tests\TestCase;
use Tests\TestDependency;

class TagsTest extends TestCase
{
    /** @test */
    public function tags_get_initialized_correctly()
    {
        $class = app(TestTags::class);

        $class->setProperties([
            'parser' => $parser = Antlers::parser(),
            'content' => 'This is the tag content',
            'context' => ['foo' => 'bar'],
            'params' => ['limit' => 3],
            'tag' => 'test:listing',
            'tag_method' => 'listing',
        ]);

        $this->assertEquals('This is the tag content', $class->content);
        $this->assertInstanceOf(Context::class, $class->context);
        $this->assertEquals(['foo' => 'bar'], $class->context->all());
        $this->assertInstanceOf(Parameters::class, $class->params);
        $this->assertEquals(['limit' => 3], $class->params->all());
        $this->assertEquals('test:listing', $class->tag);
        $this->assertEquals('listing', $class->method);
        $this->assertEquals($parser, $class->parser);
        $this->assertInstanceOf(TestDependency::class, $class->dependency);
    }

    /** @test */
    public function hooks_can_be_run()
    {
        $test = $this;

        TestTags::hook('constructed', function ($payload, $next) use ($test) {
            $test->assertEquals('initial', $payload);
            $test->assertInstanceOf(TestTags::class, $this);
            $this->setFoo('bar');

            return $next($payload);
        });

        // Do it twice to ensure that they are executed in order
        // and the tag is passed along through the closures.
        TestTags::hook('constructed', function ($payload, $next) use ($test) {
            $test->assertInstanceOf(TestTags::class, $this);
            $test->assertEquals('initial', $payload);
            $this->setFoo($this->foo.'baz');

            return $next($payload);
        });

        $class = app(TestTags::class);

        $this->assertEquals('barbaz', $class->foo);
    }

    /** @test */
    public function hooks_from_one_tag_class_dont_happen_on_another()
    {
        $hooksRan = 0;

        TestTags::hook('constructed', function ($payload, $next) use (&$hooksRan) {
            $this->setFoo('bar');
            $hooksRan++;

            return $next($this);
        });

        AnotherTestTags::hook('constructed', function ($payload, $next) use (&$hooksRan) {
            $this->setFoo($this->foo.'baz');
            $hooksRan++;

            return $next($this);
        });

        $class = app(AnotherTestTags::class);

        $this->assertEquals(1, $hooksRan);
        $this->assertEquals('baz', $class->foo);
    }
}

class TestTags extends Tags
{
    public $dependency;
    public $foo = 'initial';

    public function __construct(TestDependency $dependency)
    {
        $this->dependency = $dependency;

        $this->runHooks('constructed', $this->foo);
    }

    public function setFoo(string $foo)
    {
        $this->foo = $foo;
    }
}

class AnotherTestTags extends Tags
{
    public $foo = '';

    public function __construct()
    {
        $this->runHooks('constructed');
    }

    public function setFoo(string $foo)
    {
        $this->foo = $foo;
    }
}
