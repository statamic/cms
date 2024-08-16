<?php

namespace Tests\Data;

use ArrayAccess;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\AugmentedCollection;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Fields\Value;
use Tests\TestCase;

class HasAugmentedInstanceTest extends TestCase
{
    #[Test]
    public function it_makes_an_augmented_instance()
    {
        $augmentedCollection = new AugmentedCollection(['foo', 'bar', 'baz']);
        $filteredAugmentedCollection = new AugmentedCollection(['foo']);
        $shallowFilteredAugmentedCollection = new AugmentedCollection(['id', 'title', 'api_url']);

        $mock = $this->mock(Augmented::class);
        $mock->shouldReceive('withRelations')->with([])->andReturnSelf();
        $mock->shouldReceive('withBlueprintFields')->with([])->andReturnSelf();
        $mock->shouldReceive('get')->with('foo')->once()->andReturn(new Value('bar'));
        $mock->shouldReceive('select')->with(null)->times(2)->andReturn($augmentedCollection);
        $mock->shouldReceive('select')->with(['one'])->times(2)->andReturn($filteredAugmentedCollection);
        $mock->shouldReceive('select')->with(['id', 'title', 'api_url'])->times(1)->andReturn($shallowFilteredAugmentedCollection);

        $thing = new class($mock)
        {
            use HasAugmentedInstance;

            private $mock;

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

    #[Test]
    public function instance_runs_through_hook()
    {
        $mock = $this->mock(Augmented::class);
        $mock->shouldReceive('testing')->once();

        $mock2 = $this->mock(Augmented::class);

        $thing = new class($mock)
        {
            use HasAugmentedInstance;

            private $mock;

            public function __construct($mock)
            {
                $this->mock = $mock;
            }

            public function newAugmentedInstance(): Augmented
            {
                return $this->mock;
            }
        };

        // Call a method on the payload to make sure the payload is being passed in.
        // A different payload is intentionally being returned so that we can test the new value gets used.
        $thing::hook('augmented', function ($payload, $next) use ($mock2) {
            $payload->testing();

            return $next($mock2);
        });

        $this->assertSame($mock2, $thing->augmented());
    }

    #[Test]
    public function augmented_thing_can_define_the_default_array_keys()
    {
        $mock = $this->mock(Augmented::class);
        $mock->shouldReceive('withRelations')->with([])->andReturnSelf();
        $mock->shouldReceive('withBlueprintFields')->with([])->andReturnSelf();
        $mock->shouldReceive('select')->with(['foo', 'bar'])->once()->andReturn(new AugmentedCollection(['foo', 'bar']));

        $thing = new class($mock)
        {
            use HasAugmentedInstance;

            private $mock;

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

    #[Test]
    public function augmented_thing_can_define_the_default_relations()
    {
        $mock = $this->mock(Augmented::class);
        $mock->shouldReceive('withRelations')->with(['baz', 'qux'])->andReturnSelf();
        $mock->shouldReceive('withBlueprintFields')->with([])->andReturnSelf();
        $mock->shouldReceive('select')->with(null)->once()->andReturn(new AugmentedCollection(['foo', 'bar']));

        $thing = new class($mock)
        {
            use HasAugmentedInstance;

            private $mock;

            public function __construct($mock)
            {
                $this->mock = $mock;
            }

            public function newAugmentedInstance(): Augmented
            {
                return $this->mock;
            }

            protected function defaultAugmentedRelations()
            {
                return ['baz', 'qux'];
            }
        };

        $this->assertEquals(['foo', 'bar'], $thing->toAugmentedArray());
    }

    #[Test]
    public function it_can_check_for_array_key_existence()
    {
        $mock = $this->mock(Augmented::class);

        $thing = new class($mock) implements ArrayAccess
        {
            use HasAugmentedInstance;

            private $mock;

            public function __construct($mock)
            {
                $this->mock = $mock;
            }

            public function newAugmentedInstance(): Augmented
            {
                return $this->mock;
            }
        };

        $mock->shouldReceive('get')->with('foo')->times(2)->andReturn(new Value('bar'));
        $mock->shouldReceive('get')->with('baz')->times(2)->andReturn(new Value(null));

        $this->assertTrue(isset($thing['foo']));
        $this->assertFalse(isset($thing['baz']));

        $this->assertTrue(isset($thing->foo));
        $this->assertFalse(isset($thing->baz));
    }
}
