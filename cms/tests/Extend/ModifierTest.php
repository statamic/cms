<?php

namespace Statamic\Tests\Extend;

use Tests\TestCase;
use Tests\TestDependency;
use Statamic\Extend\Modifier;

class ModifierTest extends TestCase
{
    /** @test */
    public function tags_get_initialized_correctly()
    {
        $class = app(TestModifier::class);

        $this->assertInstanceOf(TestDependency::class, $class->dependency);
    }
}

class TestModifier extends Modifier
{
    public $dependency;

    public function __construct(TestDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}
