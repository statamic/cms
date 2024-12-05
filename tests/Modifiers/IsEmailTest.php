<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsEmailTest extends TestCase
{
    public static function emailsProvider(): array
    {
        return [
            'email' => [true, 'lknope@inpra.org'],
            'no_email' => [false, 'waffles'],
        ];
    }

    #[Test]
    #[DataProvider('emailsProvider')]
    public function it_returns_true_if_the_string_is_a_valid_email_address($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isEmail()->fetch();
    }
}
