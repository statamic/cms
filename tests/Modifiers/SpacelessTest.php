<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SpacelessTest extends TestCase
{
    public function stringsWithSpaces(): array
    {
        return [
            'spaces_and_linebreaks' => [
                '<p>I copy & pasted <a href="http://goodnightchrome.show">this link </a><strong>for you!</strong></p>',
                '<p>I copy & pasted
                    <a href="http://goodnightchrome.show">this link
                    </a>   <strong>for you!</strong>    </p>',
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider stringsWithSpaces
     */
    public function it_removes_excess_whitespace_and_linebreaks_from_string($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->spaceless()->fetch();
    }
}
