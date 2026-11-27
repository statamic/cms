<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class HexToRgbTest extends TestCase
{
    #[Test]
    public function it_converts_hex_values_to_rgb(): void
    {
        $modified = $this->modify('#FF269E');
        $this->assertEquals('255, 38, 158', $modified);

        $modified = $this->modify('01D7B0');
        $this->assertEquals('1, 215, 176', $modified);

        $modified = $this->modify('##B8FFF3');
        $this->assertEquals('184, 255, 243', $modified);
    }

    #[Test]
    public function it_converts_shorthand_hex_values_to_rgb(): void
    {
        $this->assertEquals('255, 255, 255', $this->modify('#fff'));
        $this->assertEquals('17, 34, 51', $this->modify('123'));
        $this->assertEquals('170, 187, 204', $this->modify('#abcd'));
    }

    private function modify(string $value)
    {
        return Modify::value($value)->hexToRgb()->fetch();
    }
}
