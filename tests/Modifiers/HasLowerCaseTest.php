<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class HasLowerCaseTest extends TestCase
{
    public static function stringsProvider(): array
    {
        return [
            'string_with_one_lowercase_char' => [true, "I DON'T KNOW WHAT WE'RE YELLINg ABOUT!"],
            'string_with_multiple_lowercase_chars' => [true, "I DON'T KNOw WHAT WE'RE YELLINg ABOUT!"],
            'string_with_none_lowercase_chars' => [false, "I DON'T KNOW WHAT WE'RE YELLING ABOUT!"],
        ];
    }

    #[Test]
    #[DataProvider('stringsProvider')]
    public function it_returns_true_if_the_string_has_lowercase_char_false_if_does_not($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->hasLowerCase()->fetch();
    }
}
