<?php

namespace Tests;

class TransposeCollectionMacroTest extends TestCase
{
    /** @test */
    public function it_transposes()
    {
        $before = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ];

        $after = [
            [1, 4, 7],
            [2, 5, 8],
            [3, 6, 9],
        ];

        $this->assertEquals($after, collect($before)->transpose()->all());
    }
}
