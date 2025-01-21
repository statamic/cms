<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class LastTest extends TestCase
{
    #[Test]
    #[DataProvider('stringProvider')]
    public function it_gets_the_last_n_characters_of_a_string($arg, $expected)
    {
        $this->assertEquals($expected, $this->modify('Testing', $arg));
    }

    public static function stringProvider()
    {
        return [
            [1, 'g'],
            [2, 'ng'],
            [3, 'ing'],
        ];
    }

    #[Test]
    #[DataProvider('arrayProvider')]
    public function it_gets_the_last_value_of_an_array($value, $expected)
    {
        $this->assertEquals($expected, $this->modify($value));
    }

    public static function arrayProvider()
    {
        return [
            'list' => [
                ['alfa', 'bravo', 'charlie'],
                'charlie',
            ],
            'associative' => [
                ['alfa' => 'bravo', 'charlie' => 'delta'],
                'delta',
            ],
        ];
    }

    private function modify($value, $arg = null)
    {
        return Modify::value($value)->last($arg)->fetch();
    }
}
