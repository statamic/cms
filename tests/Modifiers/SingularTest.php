<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SingularTest extends TestCase
{
    public function pluralWords(): array
    {
        return [
            ['nickle', 'nickles'],
            ['people', 'peoples'],
            ['auto', 'autos'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider pluralWords
     */
    public function it_returns_the_singular_word_of_an_english_word($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->singular()->fetch();
    }
}
