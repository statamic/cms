<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsJsonTest extends TestCase
{
    public static function bourneJsonBourneProvider(): array
    {
        return [
            'empty_json' => [true, '{}'],
            'json_string' => [true, '{"book": "All The Places You\'ll Go"}'],
            'no_json' => [false, 'foo bar baz'],
        ];
    }

    #[Test]
    #[DataProvider('bourneJsonBourneProvider')]
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
