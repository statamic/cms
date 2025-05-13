<?php

namespace Tests\Modifiers;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class LimitTest extends TestCase
{
    #[Test]
    public function it_limits_arrays(): void
    {
        $arr = ['one', 'two', 'three', 'four', 'five'];

        $this->assertEquals(['one', 'two'], $this->modify($arr, 2));
        $this->assertEquals(['one', 'two', 'three'], $this->modify($arr, 3));
    }

    #[Test]
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

    #[Test]
    public function it_limits_builders(): void
    {
        $query = \Mockery::mock(Builder::class);
        $query->shouldReceive('limit')->with(2)->once()->andReturnSelf();

        $limited = $this->modify($query, 2);
        $this->assertSame($query, $limited);
    }

    public function modify($value, $limit)
    {
        return Modify::value($value)->limit($limit)->fetch();
    }
}
