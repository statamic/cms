<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class StrPadTest extends TestCase
{
    public function paddington(): array
    {
        return [
            'pads_4_default' => ['test', 'test', [4]],
            'pads_8_default' => ['test    ', 'test', [8]],
            'pads_8_tilde_both' => ['~~test~~', 'test', [8, '~', 'both']],
            'pads_8_tilde_left' => ['~~~~test', 'test', [8, '~', 'left']],
            'pads_8_tilde_right' => ['test~~~~', 'test', [8, '~', 'right']],
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
        return Modify::value($value)->strPad($params)->fetch();
    }
}
