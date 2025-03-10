<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    #[Test]
    public function it_converts_date_into_timezone()
    {
        $this->assertEquals(
            $this->modify(Carbon::parse('2025-01-01 15:45'), 'Europe/Berlin')->format('Y-m-d H:i'),
            '2025-01-01 16:45'
        );
    }

    public function modify($value, $timezone)
    {
        return Modify::value($value)->timezone($timezone)->fetch();
    }
}
