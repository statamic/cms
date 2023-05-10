<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsJsonTest extends TestCase
{
    public function bourneJsonBourne(): array
    {
        return [
            'empty_json' => [true, '{}'],
            'json_string' => [true, '{"book": "All The Places You\'ll Go"}'],
            'no_json' => [false, 'foo bar baz'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider bourneJsonBourne
     */
    public function it_returns_true_if_string_is_valid_json($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isJson()->fetch();
    }
}
