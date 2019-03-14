<?php

namespace Tests\Tags;

use Tests\TestCase;
use Statamic\Tags\Tags;
use Statamic\API\Antlers;
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
            'parameters' => ['limit' => 3],
            'tag' => 'test:listing',
            'tag_method' => 'listing',
        ]);

        $this->assertEquals('This is the tag content', $class->content);
        $this->assertEquals(['foo' => 'bar'], $class->context);
        $this->assertEquals(['limit' => 3], $class->parameters);
        $this->assertEquals('test:listing', $class->tag);
        $this->assertEquals('listing', $class->method);
        $this->assertEquals($parser, $class->parser);
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
