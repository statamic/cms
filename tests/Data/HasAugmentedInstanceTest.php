<?php

namespace Tests;

use Statamic\Contracts\Data\Augmented;
use Statamic\Data\HasAugmentedInstance;

class HasAugmentedInstanceTest extends TestCase
{
    /** @test */
    function it_makes_an_augmented_instance()
    {
        $thing = new class {
            use HasAugmentedInstance;

            public function newAugmentedInstance(): Augmented
            {
                return new class implements Augmented {
                    public function get($key) {}
                    public function all() {}
                    public function select($keys = null) {}
                };
            }
        };

        $this->assertInstanceOf(Augmented::class, $thing->augmented());
    }
}
