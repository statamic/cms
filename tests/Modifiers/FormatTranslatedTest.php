<?php

namespace Tests\Modifiers;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FormatTranslatedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setLocale('de');

        config()->set('statamic.system.display_timezone', 'Europe/Berlin'); // +1 hour
    }

    #[Test]
    public function it_formats_date()
    {
        $this->assertSame('Mittwoch 1 Januar 2025, 15:45', $this->modify(Carbon::parse('2025-01-01 15:45'), 'l j F Y, H:i'));
    }

    public function modify($value, $format)
    {
        return Modify::value($value)->formatTranslated($format)->fetch();
    }
}
