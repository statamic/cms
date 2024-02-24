<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class LastTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider stringProvider
     */
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

    /**
     * @test
     *
     * @dataProvider arrayProvider
     */
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
