<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class FirstTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider stringProvider
     */
    public function it_gets_the_first_n_characters_of_a_string($arg, $expected)
    {
        $this->assertEquals($expected, $this->modify('Testing', $arg));
    }

    public static function stringProvider()
    {
        return [
            [1, 'T'],
            [2, 'Te'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider arrayProvider
     */
    public function it_gets_the_first_value_of_an_array($value, $expected)
    {
        $this->assertEquals($expected, $this->modify($value));
    }

    public static function arrayProvider()
    {
        return [
            'list' => [
                ['alfa', 'bravo', 'charlie'],
                'alfa',
            ],
            'associative' => [
                ['alfa' => 'bravo', 'charlie' => 'delta'],
                'bravo',
            ],
        ];
    }

    private function modify($value, $arg = null)
    {
        return Modify::value($value)->first($arg)->fetch();
    }
}
