<?php

namespace Tests\Modifiers;

use Illuminate\Support\Collection;
use Mockery;
use Statamic\Contracts\Query\Builder;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class RandomTest extends TestCase
{
    /**
     * @test
     *
     * @group array
     */
    public function it_returns_one_random_item_from_an_array(): void
    {
        $orderOfCeremony = [
            'Sonic',
            'Knuckles',
            'Tails',
        ];

        srand(1234);

        $modified = $this->modify($orderOfCeremony);
        $expected = $this->modify($orderOfCeremony);
        $this->assertEquals($expected, $modified);
    }

    /**
     * @test
     *
     * @group array
     */
    public function it_returns_one_random_item_from_a_collection(): void
    {
        $orderOfCeremony = collect([
            'Sonic',
            'Knuckles',
            'Tails',
        ]);

        srand(1234);

        $modified = $this->modify($orderOfCeremony);
        $expected = $this->modify($orderOfCeremony);
        $this->assertEquals($expected, $modified);
    }

    /** @test */
    public function it_returns_one_random_item_from_a_query_builder()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn(Collection::make([
            'Sonic',
            'Knuckles',
            'Tails',
        ]));

        srand(1234);
        $modified = $this->modify($builder);
        $expected = $this->modify($builder);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->random()->fetch();
    }
}
