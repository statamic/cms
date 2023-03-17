<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class CountTest extends TestCase
{
    public function arraysToCount(): array
    {
        return [
            [0, []],
            [1, ['foo']],
            [2, ['foo', 'bar']],
            [3, ['foo', 'bar', 'baz']],
        ];
    }

    /**
     * @test
     *
     * @dataProvider arraysToCount
     */
    public function it_counts_number_of_items_in_array($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(array $value)
    {
        return Modify::value($value)->count()->fetch();
    }
}
