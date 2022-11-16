<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsNumberwangTest extends TestCase
{
    public function numbers(): array
    {
        return [
            [true, 1],
            [true, 22],
            [true, 7],
            [true, 9],
            [true, 1002],
            [true, 2.3],
            [true, 15],
            [true, 109876567],
            [true, 31],
            [false, -10],
            [false, -0],
            [false, -100],
        ];
    }

    /**
     * @test
     * @dataProvider numbers
     */
    public function is_it_or_is_not_numberwang($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isNumberwang()->fetch();
    }
}
