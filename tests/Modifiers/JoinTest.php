<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class JoinTest extends TestCase
{
    /** @test */
    public function it_limits_arrays()
    {
        $arr = ['one', 'two', 'three', 'four', 'five'];

        $this->assertEquals(['one', 'two'], $this->modify($arr, 2));
        $this->assertEquals(['one', 'two', 'three'], $this->modify($arr, 3));
    }

    /** @test */
    public function it_returns_empty_string_when_value_is_null()
    {
        $joined = $this->modify(null);
        $this->assertEquals('', $joined);
    }

    public function modify($arr, $delimiter = ' ')
    {
        return Modify::value($arr)->join($delimiter)->fetch();
    }
}
