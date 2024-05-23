<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class KeysTest extends TestCase
{
    /** @test */
    public function it_gets_the_keys_of_an_array(): void
    {
        $input = [
            'chicken' => 'nuggets',
            'nuggets' => 'Denver',
        ];

        $expected = [
            'chicken',
            'nuggets',
        ];
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(array $value)
    {
        return Modify::value($value)->keys()->fetch();
    }
}
