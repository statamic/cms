<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class Nl2brTest extends TestCase
{
    public static function stringsProvider(): array
    {
        return [
            ["This is a summary <br />\n on multiple lines", "This is a summary \n on multiple lines"],
            ['This is a summary on multiple lines', 'This is a summary on multiple lines'],
        ];
    }

    #[Test]
    #[DataProvider('stringsProvider')]
    public function it_replaces_linebreaks_with_br_tags($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->nl2br()->fetch();
    }
}
