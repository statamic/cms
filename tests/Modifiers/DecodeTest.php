<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class DecodeTest extends TestCase
{
    public function undecoded(): array
    {
        return [
            ['I\'ll "eat" the <b>bacon</b> now', "I'll \"eat\" the <b>bacon</b> now"],
        ];
    }

    /**
     * @test
     *
     * @dataProvider undecoded
     */
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
