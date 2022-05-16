<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsLeapYearTest extends TestCase
{
    /** @test */
    public function it_returns_true_if_date_is_in_a_leap_year(): void
    {
        $leapYear = 'November 2016';
        $modified = $this->modify($leapYear);
        $this->assertTrue($modified);
    }

    /** @test */
    public function it_returns_false_if_date_is_not_in_a_leap_year(): void
    {
        $leapYear = 'November 2017';
        $modified = $this->modify($leapYear);
        $this->assertFalse($modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isLeapYear()->fetch();
    }
}
