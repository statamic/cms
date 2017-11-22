<?php

namespace Tests\Extend;

use Tests\TestCase;
use Tests\TestDependency;
use Statamic\Extend\Filter;

class FilterTest extends TestCase
{
    /** @test */
    public function filter_gets_initialized_correctly()
    {
        $class = app(TestFilter::class);

        $this->assertInstanceOf(TestDependency::class, $class->dependency);
    }
}

class TestFilter extends Filter
{
    public $dependency;

    public function __construct(TestDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}
