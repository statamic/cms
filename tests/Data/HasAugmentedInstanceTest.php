<?php

namespace Tests\Data;

use Statamic\Contracts\Data\Augmented;
use Statamic\Data\AugmentedCollection;
use Statamic\Data\HasAugmentedInstance;
use Tests\TestCase;

class HasAugmentedInstanceTest extends TestCase
{
    /** @test */
    public function it_makes_an_augmented_instance()
    {
        $augmentedCollection = new AugmentedCollection(['foo', 'bar', 'baz']);
        $filteredAugmentedCollection = new AugmentedCollection(['foo']);
        $shallowFilteredAugmentedCollection = new AugmentedCollection(['id', 'title', 'api_url']);

        $mock = $this->mock(Augmented::class);
        $mock->shouldReceive('get')->with('foo')->once()->andReturn('bar');
        $mock->shouldReceive('select')->with(null)->times(2)->andReturn($augmentedCollection);
        $mock->shouldReceive('select')->with(['one'])->times(2)->andReturn($filteredAugmentedCollection);
        $mock->shouldReceive('select')->with(['id', 'title', 'api_url'])->times(1)->andReturn($shallowFilteredAugmentedCollection);

        $thing = new class($mock)
        {
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

        $this->assertEquals($augmentedCollection, $thing->toAugmentedCollection());
        $this->assertEquals($filteredAugmentedCollection, $thing->toAugmentedCollection(['one']));
        $this->assertFalse($augmentedCollection->hasShallowNesting());

        $collection = $thing->toShallowAugmentedCollection();
        $this->assertEquals($shallowFilteredAugmentedCollection, $collection);
        $this->assertTrue($collection->hasShallowNesting());
    }

    /** @test */
    public function augmented_thing_can_define_the_default_array_keys()
    {
        $mock = $this->mock(Augmented::class);
        $mock->shouldReceive('select')->with(['foo', 'bar'])->once()->andReturn(new AugmentedCollection(['foo', 'bar']));

        $thing = new class($mock)
        {
            use HasAugmentedInstance;

            public function __construct($mock)
            {
                $this->mock = $mock;
            }

            public function newAugmentedInstance(): Augmented
            {
                return $this->mock;
            }

            protected function defaultAugmentedArrayKeys()
            {
                return ['foo', 'bar'];
            }
        };

        $this->assertEquals(['foo', 'bar'], $thing->toAugmentedArray());
    }
}
