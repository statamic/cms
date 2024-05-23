<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ValuesTest extends TestCase
{
    /** @test */
    public function it_gets_the_values_of_an_array(): void
    {
        $input = [
            'chicken' => 'nuggets',
            'nuggets' => 'Denver',
        ];

        $expected = [
            'nuggets',
            'Denver',
        ];
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(array $value)
    {
        return Modify::value($value)->values()->fetch();
    }
}
