<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FloorTest extends TestCase
{
    public function numbersPool(): array
    {
        return [
            [-10, -10],
            [-10, -9.5],
            [0, 0],
            [1, 1.2],
            [25, 25],
            [25, 25.3],
            [25, 25.5],
            [25, 25.98],
            [25, '25.98'],
        ];
    }

    /**
     * @test
     * @dataProvider numbersPool
     */
    public function it_rounds_a_number_down_to_next_whole_number($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->floor()->fetch();
    }
}
