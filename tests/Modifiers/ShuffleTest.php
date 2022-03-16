<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ShuffleTest extends TestCase
{
    /**
     * @test
     * @group array
     */
    public function it_shuffles_the_items_of_an_array(): void
    {
        $orderOfCeremony = [
            'Sonic',
            'Knuckles',
            'Tails',
        ];
        $modified = $this->modify($orderOfCeremony, [1234]);
        $expected = $this->modify($orderOfCeremony, [1234]);
        $this->assertEquals($expected, $modified);
    }

    /**
     * @test
     * @group array
     */
    public function it_shuffles_the_items_of_a_collection(): void
    {
        $orderOfCeremony = collect([
            'Sonic',
            'Knuckles',
            'Tails',
        ]);
        $modified = $this->modify($orderOfCeremony, [1234]);
        $expected = $this->modify($orderOfCeremony, [1234]);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value, $params)
    {
        return Modify::value($value)->shuffle($params)->fetch();
    }
}
