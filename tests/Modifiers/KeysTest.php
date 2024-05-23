<?php

namespace Tests\Modifiers;

use Illuminate\Support\Collection;
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

        $modified = $this->modify($input);
        $this->assertEquals(['chicken', 'nuggets'], $modified);
    }

    /** @test */
    public function it_gets_the_keys_of_a_collection(): void
    {
        $input = collect([
            'chicken' => 'nuggets',
            'nuggets' => 'Denver',
        ]);

        $modified = $this->modify($input);
        $this->assertInstanceOf(Collection::class, $modified);
        $this->assertEquals(['chicken', 'nuggets'], $modified->all());
    }

    private function modify($value)
    {
        return Modify::value($value)->keys()->fetch();
    }
}
