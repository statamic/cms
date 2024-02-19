<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsNumericTest extends TestCase
{
    public static function numbersProvider(): array
    {
        return [
            'numbers' => [true, 4815162342],
            'numbers_as_string' => [true, '4815162342'],
            'numbers_with_chars' => [false, 'just type 4 8 15 16 23 42'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider numbersProvider
     */
    public function it_returns_true_if_value_is_number_or_numeric_string($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isNumeric()->fetch();
    }
}
