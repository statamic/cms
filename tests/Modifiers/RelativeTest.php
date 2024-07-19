<?php

namespace Tests\Modifiers;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class RelativeTest extends TestCase
{
    #[Test]
    public function it_converts_a_date_to_relative()
    {
        Carbon::setTestNow('2024-06-30');

        $date = new Carbon('2024-05-30');

        $this->assertEquals('1 month ago', $this->modify($date));
    }

    #[Test]
    public function it_converts_a_date_to_relative_without_modifiers()
    {
        Carbon::setTestNow('2024-06-30');

        $date = new Carbon('2024-05-30');

        $this->assertEquals('1 month', $this->modify($date, true));
        $this->assertEquals('1 month', $this->modify($date, 'true'));
    }

    protected function modify($arr, ...$args)
    {
        return Modify::value($arr)->relative($args)->fetch();
    }
}
