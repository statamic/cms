<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ModifyDateTest extends TestCase
{
    #[Test]
    public function it_modifies_date()
    {
        $this->assertEquals($this->modify(Carbon::parse('2025-01-01'), '+2 months')->format('Y-m-d'), '2025-03-01');
    }

    public function modify($value, $modify)
    {
        return Modify::value($value)->modifyDate($modify)->fetch();
    }
}
