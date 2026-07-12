<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsLowercaseTest extends TestCase
{
    public static function stringsProvider(): array
    {
        return [
            'string_with_one_uppercase_char' => [false, "i don't know what we're yellinG about!"],
            'string_with_multiple_uppercase_chars' => [false, "i don't know what we're yellinG About!"],
            'string_with_all_uppercase_chars' => [false, "I DON'T KNOW WHAT WE'RE YELLING ABOUT!"],
            'string_all_lowercase_with_punctuation' => [false, 'fhqwhgads!'],
            'string_all_lowercase' => [true, 'fhqwhgads'],
        ];
    }

    #[Test]
    #[DataProvider('stringsProvider')]
    public function it_returns_true_if_string_has_only_lowercase_chars($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isLowercase()->fetch();
    }
}
