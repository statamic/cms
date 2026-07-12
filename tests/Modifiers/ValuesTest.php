<?php

namespace Tests\Modifiers;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ValuesTest extends TestCase
{
    #[Test]
    public function it_gets_the_values_of_an_array(): void
    {
        $input = [
            'chicken' => 'nuggets',
            'nuggets' => 'Denver',
        ];

        $modified = $this->modify($input);
        $this->assertEquals(['nuggets', 'Denver'], $modified);
    }

    #[Test]
    public function it_gets_the_values_of_a_collection(): void
    {
        $input = collect([
            'chicken' => 'nuggets',
            'nuggets' => 'Denver',
        ]);

        $modified = $this->modify($input);
        $this->assertInstanceOf(Collection::class, $modified);
        $this->assertEquals(['nuggets', 'Denver'], $modified->all());
    }

    private function modify($value)
    {
        return Modify::value($value)->values()->fetch();
    }
}
