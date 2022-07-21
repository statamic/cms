<?php

namespace Tests\Modifiers;

use Illuminate\Support\Collection;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * @group array
 */
class LimitTest extends TestCase
{
    /** @test */
    public function it_limits_arrays(): void
    {
        $arr = ['one', 'two', 'three', 'four', 'five'];

        $this->assertEquals(['one', 'two'], $this->modify($arr, 2));
        $this->assertEquals(['one', 'two', 'three'], $this->modify($arr, 3));
    }

    /** @test */
    public function it_limits_collections(): void
    {
        $collection = collect(['one', 'two', 'three', 'four', 'five']);

        $limited = $this->modify($collection, 2);
        $this->assertInstanceOf(Collection::class, $limited);
        $this->assertEquals(['one', 'two'], $limited->all());

        $limited = $this->modify($collection, 3);
        $this->assertInstanceOf(Collection::class, $limited);
        $this->assertEquals(['one', 'two', 'three'], $limited->all());
    }

    public function modify($arr, $limit)
    {
        return Modify::value($arr)->limit($limit)->fetch();
    }
}
