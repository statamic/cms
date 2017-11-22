<?php

namespace Statamic\Tests\Extend;

use Tests\TestCase;
use Statamic\Extend\Tags;
use Tests\TestDependency;

class TagsTest extends TestCase
{
    /** @test */
    public function tags_get_initialized_correctly()
    {
        $class = app(TestTags::class);

        $class->setProperties([
            'content' => 'This is the tag content',
            'context' => ['foo' => 'bar'],
            'parameters' => ['limit' => 3],
            'tag' => 'test:listing',
            'tag_method' => 'listing',
        ]);

        $this->assertEquals('This is the tag content', $class->content);
        $this->assertEquals(['foo' => 'bar'], $class->context);
        $this->assertEquals(['limit' => 3], $class->parameters);
        $this->assertEquals('test:listing', $class->tag);
        $this->assertEquals('listing', $class->tag_method);
        $this->assertInstanceOf(TestDependency::class, $class->dependency);
    }
}

class TestTags extends Tags
{
    public $dependency;

    public function __construct(TestDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}
