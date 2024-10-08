<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SmartypantsTest extends TestCase
{
    public static function dumbCharsProvider(): array
    {
        return [
            [
                '&#8220;What&#8217;s your favorite album?&#8221; asked Lars. &#8220;&#8230;And Justice for All&#8221; replied Kirk &#8212; who was icing his hands after a 20 minute guitar solo.',
                '"What\'s your favorite album?" asked Lars. ``...And Justice for All\'\' replied Kirk -- who was icing his hands after a 20 minute guitar solo.',
            ],
        ];
    }

    #[Test]
    #[DataProvider('dumbCharsProvider')]
    public function it_translates_plain_ascii_chars_into_smart_punctuation($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->smartypants()->fetch();
    }
}
