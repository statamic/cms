<?php

namespace Tests\Extend;

use Tests\TestCase;
use Tests\TestDependency;
use Statamic\Extend\Widget;

class WidgetTest extends TestCase
{
    /** @test */
    public function widget_get_initialized_correctly()
    {
        $class = app(TestWidget::class);

        $class->setParameters(['foo' => 'bar']);

        $this->assertEquals('bar', $class->getParam('foo'));
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
