<?php

namespace Tests\Modifiers;

use Mockery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ReverseTest extends TestCase
{
    #[Test]
    #[Group('array')]
    public function it_reverses_the_items_of_an_array(): void
    {
        $orderOfCeremony = [
            'photos',
            'service',
            'eat',
            'party',
        ];
        $modified = $this->modify($orderOfCeremony);
        $expected = [
            'party',
            'eat',
            'service',
            'photos',
        ];
        $this->assertEquals($expected, $modified);
    }

    #[Test]
    #[Group('array')]
    public function it_reverses_the_items_of_a_collection(): void
    {
        $orderOfCeremony = collect([
            'photos',
            'service',
            'eat',
            'party',
        ]);
        $modified = $this->modify($orderOfCeremony);
        $expected = [
            'party',
            'eat',
            'service',
            'photos',
        ];
        $this->assertEquals($expected, $modified);
    }

    #[Test]
    public function it_reverses_items_from_query_builder(): void
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn(collect([
            'photos',
            'service',
            'eat',
            'party',
        ]));

        $modified = $this->modify($builder);
        $expected = [
            'party',
            'eat',
            'service',
            'photos',
        ];
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->reverse()->fetch();
    }
}
