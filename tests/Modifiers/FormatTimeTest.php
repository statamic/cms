<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FormatTimeTest extends TestCase
{
    #[Test]
    public function it_formats_time_with_default_format()
    {
        $this->assertSame('2:45pm', $this->modify('14:45'));
    }

    #[Test]
    public function it_formats_time_with_custom_format()
    {
        $this->assertSame('2:45 PM', $this->modify('14:45', 'g:i A'));
    }

    #[Test]
    public function it_formats_time_with_seconds()
    {
        $this->assertSame('2:45:30pm', $this->modify('14:45:30', 'g:i:sa'));
    }

    #[Test]
    public function it_does_not_apply_timezone_conversion()
    {
        config()->set('statamic.system.display_timezone', 'Europe/Berlin');
        config()->set('statamic.system.localize_dates_in_modifiers', true);

        $this->assertSame('2:45pm', $this->modify('14:45', 'g:ia'));
    }

    #[Test]
    public function it_returns_null_for_null_value()
    {
        $this->assertNull($this->modify(null, 'g:ia'));
    }

    #[Test]
    public function it_returns_empty_string_for_empty_string_value()
    {
        $this->assertSame('', $this->modify('', 'g:ia'));
    }

    public function modify($value, $format = null)
    {
        return Modify::value($value)->formatTime($format)->fetch();
    }
}
