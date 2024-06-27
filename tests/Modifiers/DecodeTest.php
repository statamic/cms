<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class DecodeTest extends TestCase
{
    public static function undecodedProvider(): array
    {
        return [
            ['I\'ll "eat" the <b>bacon</b> now', "I'll \"eat\" the <b>bacon</b> now"],
        ];
    }

    #[Test]
    #[DataProvider('undecodedProvider')]
    public function it_converts_all_html_entities_to_applicable_chars($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->decode()->fetch();
    }
}
