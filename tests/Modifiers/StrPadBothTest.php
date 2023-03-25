<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class StrPadBothTest extends TestCase
{
    public function paddington(): array
    {
        return [
            'pads_5_tilde' => ['test~', 'test', [5, '~']],
            'pads_8_plus' => ['++test++', 'test', [8, '+']],
            'pads_4_two' => ['2022', '02', [4, '2']],
        ];
    }

    /**
     * @test
     *
     * @dataProvider paddington
     */
    public function it_pads_a_string(string $expected, string $input, array $params): void
    {
        $modified = $this->modify($input, $params);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value, array $params): string
    {
        return Modify::value($value)->strPadBoth($params)->fetch();
    }
}
