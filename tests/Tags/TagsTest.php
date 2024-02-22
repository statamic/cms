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
        TestTags::addHook('constructed', function ($tag, $payload) {
            $this->assertInstanceOf(TestTags::class, $tag);
            $this->assertEquals('initial', $payload);
            $tag->setFoo('bar');
        });

        // Do it twice to ensure that they are executed in order
        // and the tag is passed along through the closures.
        TestTags::addHook('constructed', function ($tag, $payload) {
            $this->assertInstanceOf(TestTags::class, $tag);
            $this->assertEquals('initial', $payload);
            $tag->setFoo($tag->foo.'baz');
        });

        $class = app(TestTags::class);

        $this->assertEquals('barbaz', $class->foo);
    }

    /** @test */
    public function hooks_from_one_tag_class_dont_happen_on_another()
    {
        $hooksRan = 0;

        TestTags::addHook('constructed', function ($tag) use (&$hooksRan) {
            $tag->setFoo('bar');
            $hooksRan++;
        });

        AnotherTestTags::addHook('constructed', function ($tag) use (&$hooksRan) {
            $tag->setFoo($tag->foo.'baz');
            $hooksRan++;
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

        $this->runHook('constructed', $this->foo);
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
        $this->runHook('constructed');
    }

    public function setFoo(string $foo)
    {
        $this->foo = $foo;
    }
}
