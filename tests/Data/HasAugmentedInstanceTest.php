<?php

namespace Tests;

use Statamic\Contracts\Data\Augmented;
use Statamic\Data\HasAugmentedInstance;

class HasAugmentedInstanceTest extends TestCase
{
    /** @test */
    function it_makes_an_augmented_instance()
    {
        $mock = $this->mock(Augmented::class);
        $mock->shouldReceive('get')->with('foo')->once()->andReturn('bar');
        $mock->shouldReceive('select')->with(null)->once()->andReturn(['foo', 'bar', 'baz']);
        $mock->shouldReceive('select')->with(['one'])->once()->andReturn(['foo']);

        $thing = new class($mock) {
            use HasAugmentedInstance;

            public function __construct($mock)
            {
                $this->mock = $mock;
            }

            public function newAugmentedInstance(): Augmented
            {
                return $this->mock;
            }
        };

        $this->assertInstanceOf(Augmented::class, $thing->augmented());

        $this->assertEquals('bar', $thing->augmentedValue('foo'));
        $this->assertEquals(['foo', 'bar', 'baz'], $thing->toAugmentedArray());
        $this->assertEquals(['foo'], $thing->toAugmentedArray(['one']));
    }
}
