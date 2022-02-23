<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class CeilTest extends TestCase
{
    public function numbersPool(): array
    {
        return [
            [-10, -10],
            [-9, -9.5],
            [0, 0],
            [2, 1.2],
            [25, 25],
            [26, 25.3],
            [26, 25.5],
            [26, 25.98],
            [26, '25.98'],
        ];
    }

    /**
     * @test
     * @dataProvider numbersPool
     */
    public function it_rounds_a_number_up_to_next_whole_number($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->ceil()->fetch();
    }
}
