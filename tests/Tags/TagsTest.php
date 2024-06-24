<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Statamic\Tags\Tags;
use Tests\TestCase;
use Tests\TestDependency;

class TagsTest extends TestCase
{
    #[Test]
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
}

class TestTags extends Tags
{
    public $dependency;

    public function __construct(TestDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}
