<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsoFormatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.system.display_timezone', 'Europe/Berlin'); // +1 hour
    }

    #[Test]
    public function it_formats_date()
    {
        $this->assertSame('2025.01.01 15:45', $this->modify(Carbon::parse('2025-01-01 15:45'), 'YYYY.MM.DD HH:mm'));
    }

    public function modify($value, $format)
    {
        return Modify::value($value)->isoFormat($format)->fetch();
    }
}
