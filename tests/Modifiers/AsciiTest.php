<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class AsciiTest extends TestCase
{
    #[Test]
    public function it_replaces_non_ascii_chars_with_closest_counterparts(): void
    {
        $modified = $this->modify('lemoñade');
        $this->assertEquals('lemonade', $modified);

        $modified = $this->modify('£');
        $this->assertEquals('', $modified);

        $modified = $this->modify('µ');
        $this->assertEquals('u', $modified);

        $modified = $this->modify('ø');
        $this->assertEquals('o', $modified);

        // German umlauts
        $modified = $this->modify('äöüß');
        $this->assertEquals('aouss', $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->ascii()->fetch();
    }
}
