<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FlipTest extends TestCase
{
    /** @test */
    public function it_flips_array_key_with_value(): void
    {
        $input = [
            'chicken' => 'nuggets',
            'nuggets' => 'Denver',
        ];

        $expected = [
            'nuggets' => 'chicken',
            'Denver' => 'nuggets',
        ];
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(array $value)
    {
        return Modify::value($value)->flip()->fetch();
    }
}
