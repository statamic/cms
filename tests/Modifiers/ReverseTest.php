<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ReverseTest extends TestCase
{
    /**
     * @test
     * @group array
     */
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

    /**
     * @test
     * @group array
     */
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

    private function modify($value)
    {
        return Modify::value($value)->reverse()->fetch();
    }
}
