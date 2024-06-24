<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class BackgroundPositionTest extends TestCase
{
    public static function backgroundPositionsProvider(): array
    {
        return [
            ['0% 0%', '0-0'],
            ['100% 100%', '100-100'],
            ['50% 50%', '50-50'],
            ['0% 50%', '0-50'],
            ['50% 0%', '50-0'],
            // ['0% 0%', '-10--10'], // TODO: This returns '0% 10%' as it not detect the double minus sign
            ['150% 200%', '150-200'],
            ['150', '150'],
            ['150+200', '150+200'],
        ];
    }

    #[Test]
    #[DataProvider('backgroundPositionsProvider')]
    public function it_converts_a_focus_point_into_css_compatible_percent_value($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->backgroundPosition()->fetch();
    }
}
