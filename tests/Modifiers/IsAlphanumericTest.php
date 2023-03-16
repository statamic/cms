<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsAlphanumericTest extends TestCase
{
    public function strings(): array
    {
        return [
            'string_with_numbers' => [true, '123'],
            'string_with_chars' => [true, 'abc'],
            'string_with_numbers_and_chars' => [true, 'abc123'],
            'string_with_numbers_chars_and_other_symbols' => [false, 'abc123!@#'],
            'string_other_symbols' => [false, '!@#'],

        ];
    }

    /**
     * @test
     *
     * @dataProvider strings
     */
    public function it_returns_true_if_the_string_contains_only_alphanumeric_chars($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isAlphanumeric()->fetch();
    }
}
