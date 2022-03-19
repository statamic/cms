<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class CollapseWhitespaceTest extends TestCase
{
    public function stringsWithWeirdWhitespaces(): array
    {
        return [
            'multiple_whitespace' => ['Bad at typing', 'Bad   at           typing'],
            'surrounding_whitespaces' => ['Bad at typing', '  Bad   at           typing    '],
            'tabs_and_newlines_chars' => ['Bad at typing', "Bad at \ttyping\n"],
            'multibyte_whitespace' => ['ラメ単色', '　ラメ単色'],
            'thin_whitespace' => ['Bad at typing', 'Bad at typing'],
            'ideographic_whitespace' => ['Bad at typing', 'Bad　at　typing'],
        ];
    }

    /**
     * @test
     * @dataProvider stringsWithWeirdWhitespaces
     */
    public function it_collapses_whitespaces($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->collapseWhitespace()->fetch();
    }
}
