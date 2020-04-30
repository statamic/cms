<?php

namespace Tests\Widgets;

use Statamic\Widgets\Widget;
use Tests\TestCase;
use Tests\TestDependency;

class WidgetTest extends TestCase
{
    /** @test */
    public function widget_get_initialized_correctly()
    {
        $class = app(TestWidget::class);

        $class->setConfig(['foo' => 'bar']);

        $this->assertEquals('bar', $class->config('foo'));
        $this->assertInstanceOf(TestDependency::class, $class->dependency);
    }
}

class TestWidget extends Widget
{
    public $dependency;

    public function __construct(TestDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}
