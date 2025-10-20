<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FormatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.system.display_timezone', 'Europe/Berlin'); // +1 hour
    }

    #[Test]
    public function it_formats_date()
    {
        $this->assertSame('1st January 2025 3:45pm', $this->modify(Carbon::parse('2025-01-01 15:45'), 'jS F Y g:ia'));
    }

    #[Test]
    public function it_formats_date_and_outputs_in_display_timezone()
    {
        config()->set('statamic.system.localize_dates_in_modifiers', true);

        $this->assertSame('1st January 2025 4:45pm', $this->modify(Carbon::parse('2025-01-01 15:45'), 'jS F Y g:ia'));
    }

    public function modify($value, $format)
    {
        return Modify::value($value)->format($format)->fetch();
    }
}
