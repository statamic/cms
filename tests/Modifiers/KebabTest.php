<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class KebabTest extends TestCase
{
    public static function stringsProvider(): array
    {
        return [
            'with_whitespaces' => ['just-because-i-can', 'Just Because I Can'],
            'with_underscores_chars' => ['just_-because_-i_-can', 'Just_Because_I_Can'],
            'surrounding_spaces' => ['just-because-i-can', ' Just Because I Can '],
            'before_uppercase_chars' => ['just-because-i-can', 'JustBecauseICan'],
        ];
    }

    #[Test]
    #[DataProvider('stringsProvider')]
    public function it_converts_the_value_to_kebab_case($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->kebab()->fetch();
    }
}
