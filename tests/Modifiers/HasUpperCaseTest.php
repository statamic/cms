<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class HasUpperCaseTest extends TestCase
{
    public static function stringsProvider(): array
    {
        return [
            'string_with_one_uppercase_char' => [true, "i don't know what we're yellinG about!"],
            'string_with_multiple_uppercase_chars' => [true, "i don't know what we're yellinG About!"],
            'string_with_all_uppercase_chars' => [true, "I DON'T KNOW WHAT WE'RE YELLING ABOUT!"],
            'string_with_none_uppercase_chars' => [false, "i don't know what we're yelling about!"],
        ];
    }

    #[Test]
    #[DataProvider('stringsProvider')]
    public function it_returns_true_if_the_string_has_uppercase_char_false_if_does_not($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->hasUpperCase()->fetch();
    }
}
