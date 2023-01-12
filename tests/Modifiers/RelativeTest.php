<?php

namespace Tests\Modifiers;

use Carbon\Carbon;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class RelativeTest extends TestCase
{
    /** @test */
    public function it_converts_a_date_to_relative()
    {
        $date = new Carbon('today -1 year');

        $this->assertEquals('1 year ago', $this->modify($date));
    }

    /** @test */
    public function it_converts_a_date_to_relative_without_modifiers()
    {
        $date = new Carbon('today -1 year');

        $this->assertEquals('1 year', $this->modify($date, true));
        $this->assertEquals('1 year', $this->modify($date, 'true'));
    }

    protected function modify($arr, ...$args)
    {
        return Modify::value($arr)->relative($args)->fetch();
    }
}
