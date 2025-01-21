<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class CollapseTest extends TestCase
{
    #[Test]
    public function it_return_empty_array_when_simple_array_given(): void
    {
        $actual = ['one', 'two', 'three'];
        $expected = [];
        $modified = $this->modify($actual);
        $this->assertEquals($expected, $modified);
    }

    #[Test]
    public function it_collapses_an_array_of_arrays(): void
    {
        $actual = [
            ['one', 'two', 'three'],
            ['four', 'five', 'six'],
        ];
        $expected = ['one', 'two', 'three', 'four', 'five', 'six'];
        $modified = $this->modify($actual);

        $this->assertEquals($expected, $modified);
    }

    private function modify(array $value)
    {
        return Modify::value($value)->collapse()->fetch();
    }
}
