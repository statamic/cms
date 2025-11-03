<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class SortTest extends TestCase
{
    #[Test]
    public function it_sorts_primitive_arrays()
    {
        $arr = ['beta', 'zeta', 'alpha'];
        $expected = ['alpha', 'beta', 'zeta'];

        $this->assertEquals($expected, $this->modify($arr));
        $this->assertEquals($expected, $this->modify($arr, 'true'));
        $this->assertEquals($expected, $this->modify($arr, 'true'));
        $this->assertEquals($expected, $this->modify($arr, ['true', 'asc']));

        $this->assertEquals($expected, $this->modify(collect($arr)));
        $this->assertEquals($expected, $this->modify(collect($arr), 'true'));
        $this->assertEquals($expected, $this->modify(collect($arr), ['true', 'asc']));
    }

    #[Test]
    public function it_sorts_primitive_arrays_descending()
    {
        $arr = ['beta', 'zeta', 'alpha'];
        $expected = ['zeta', 'beta', 'alpha'];

        $this->assertEquals($expected, $this->modify($arr, ['true', 'desc']));
        $this->assertEquals($expected, $this->modify(collect($arr), ['true', 'desc']));
    }

    #[Test]
    public function it_sorts_multidimensional_arrays_by_a_key()
    {
        $arr = [
            ['value' => 'beta'],
            ['value' => 'zeta'],
            ['value' => 'alpha'],
        ];

        $expected = [
            ['value' => 'alpha'],
            ['value' => 'beta'],
            ['value' => 'zeta'],
        ];

        $this->assertEquals($expected, $this->modify($arr, 'value'));
        $this->assertEquals($expected, $this->modify(collect($arr), 'value'));
    }

    #[Test]
    public function it_sorts_multidimensional_arrays_by_a_key_descending()
    {
        $arr = [
            ['value' => 'beta'],
            ['value' => 'zeta'],
            ['value' => 'alpha'],
        ];

        $expected = [
            ['value' => 'zeta'],
            ['value' => 'beta'],
            ['value' => 'alpha'],
        ];

        $this->assertEquals($expected, $this->modify($arr, ['value', 'desc']));
        $this->assertEquals($expected, $this->modify(collect($arr), ['value', 'desc']));
    }

    #[Test]
    public function it_sorts_primitive_arrays_randomly()
    {
        $this->assertSortsRandomly(['alpha', 'beta', 'zeta']);
    }

    #[Test]
    public function it_sorts_multidimensional_arrays_randomly()
    {
        $this->assertSortsRandomly([
            ['word' => 'alpha'],
            ['word' => 'beta'],
            ['word' => 'zeta'],
        ]);
    }

    private function assertSortsRandomly($arr)
    {
        $combinations = [];

        foreach (collect()->times(100) as $time) {
            $modified = $this->modify($arr, 'random');

            if (collect($modified)->keys()->first() != 0) {
                $this->fail('Collection is not zero indexed');
            }

            $combinations[json_encode($modified)] = true;
        }

        $this->assertNotCount(1, $combinations);
    }

    #[Test]
    public function it_sorts_builders(): void
    {
        $query = \Mockery::mock(Builder::class);

        $query->shouldReceive('inRandomOrder')->once()->andReturnSelf();
        $limited = $this->modify($query, 'random');
        $this->assertSame($query, $limited);

        $query->shouldReceive('orderBy')->with('title', 'asc')->once()->andReturnSelf();
        $limited = $this->modify($query, 'title');
        $this->assertSame($query, $limited);

        $query->shouldReceive('orderBy')->with('title', 'desc')->once()->andReturnSelf();
        $limited = $this->modify($query, ['title', 'desc']);
        $this->assertSame($query, $limited);
    }

    public function modify($value, $args = [])
    {
        $modified = Modify::value($value)->sort($args)->fetch();

        // It gets modified to a Collection, but it's easier to write tests as arrays.
        return $value instanceof Builder ? $modified : $modified->all();
    }
}
