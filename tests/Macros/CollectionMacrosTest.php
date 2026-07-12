<?php

namespace Tests\Macros;

use Illuminate\Contracts\Support\Arrayable;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Data\Augmentable;
use Tests\TestCase;

class CollectionMacrosTest extends TestCase
{
    #[Test]
    public function to_augmented_array()
    {
        $one = tap($this->mock(Augmentable::class), function ($m) {
            $m->shouldReceive('toAugmentedArray')->with(null)->once()->andReturn('first');
            $m->shouldNotReceive('toArray');
        });

        $two = tap($this->mock(Augmentable::class), function ($m) {
            $m->shouldReceive('toAugmentedArray')->with(null)->once()->andReturn('second');
            $m->shouldNotReceive('toArray');
        });

        $three = tap($this->mock(Arrayable::class), function ($m) {
            $m->shouldNotReceive('toAugmentedArray');
            $m->shouldReceive('toArray')->once()->andReturn('third');
        });

        $this->assertEquals(
            ['first', 'second', 'third'],
            collect([$one, $two, $three])->toAugmentedArray()
        );
    }

    #[Test]
    public function to_augmented_array_with_selected_keys()
    {
        $one = tap($this->mock(Augmentable::class), function ($m) {
            $m->shouldReceive('toAugmentedArray')->with(['foo', 'bar'])->once()->andReturn('first');
            $m->shouldNotReceive('toArray');
        });

        $two = tap($this->mock(Augmentable::class), function ($m) {
            $m->shouldReceive('toAugmentedArray')->with(['foo', 'bar'])->once()->andReturn('second');
            $m->shouldNotReceive('toArray');
        });

        $three = tap($this->mock(Arrayable::class), function ($m) {
            $m->shouldNotReceive('toAugmentedArray');
            $m->shouldReceive('toArray')->once()->andReturn('third');
        });

        $this->assertEquals(
            ['first', 'second', 'third'],
            collect([$one, $two, $three])->toAugmentedArray(['foo', 'bar'])
        );
    }
}
