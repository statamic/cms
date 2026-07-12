<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsUppercaseTest extends TestCase
{
    public static function stringsProvider(): array
    {
        return [
            'string_all_uppercase' => [true, 'NOISES'],
            'string_with_one_lowercase' => [false, 'NOIsES'],
            'string_all_uppercase_with_punctuation' => [false, "I DON'T KNOW WHAT WE'RE YELLING ABOUT!"],
            'string_with_one_uppercase_char' => [false, "i don't know what we're yellinG about!"],
            'string_with_multiple_uppercase_chars' => [false, "i don't know what we're yellinG About!"],
        ];
    }

    #[Test]
    #[DataProvider('stringsProvider')]
    public function it_returns_true_if_string_has_only_uppercase_chars($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isUppercase()->fetch();
    }
}
