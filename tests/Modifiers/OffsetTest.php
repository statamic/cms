<?php

namespace Tests\Modifiers;

use Illuminate\Support\Collection;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * @group array
 */
class OffsetTest extends TestCase
{
    /** @test */
    public function it_offsets_arrays(): void
    {
        $arr = ['one', 'two', 'three'];

        $this->assertEquals(['two', 'three'], $this->modify($arr, 1));
        $this->assertEquals(['three'], $this->modify($arr, 2));
    }

    /** @test */
    public function it_offsets_collections(): void
    {
        $collection = collect(['one', 'two', 'three']);

        $limited = $this->modify($collection, 1);
        $this->assertInstanceOf(Collection::class, $limited);
        $this->assertEquals(['two', 'three'], $limited->all());

        $limited = $this->modify($collection, 2);
        $this->assertInstanceOf(Collection::class, $limited);
        $this->assertEquals(['three'], $limited->all());
    }

    public function modify($arr, $limit)
    {
        return Modify::value($arr)->offset($limit)->fetch();
    }
}
