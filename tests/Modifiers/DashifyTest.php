<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class DashifyTest extends TestCase
{
    public static function undashyThingsProvider(): array
    {
        return [
            'with_whitespaces' => ['just-because-i-can', 'Just Because I Can'],
            'with_underscores_chars' => ['just-because-i-can', 'Just_Because_I_Can'],
            'surrounding_spaces' => ['just-because-i-can', ' Just Because I Can '],
            'before_uppercase_chars' => ['just-because-i-can', 'JustBecauseICan'],
        ];
    }

    #[Test]
    #[DataProvider('undashyThingsProvider')]
    public function it_returns_a_lowercase_and_trimmed_string_separated_by_dashes($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->dashify()->fetch();
    }
}
