<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class StrPadRightTest extends TestCase
{
    public static function padProvider(): array
    {
        return [
            'pads_5_tilde' => ['test~', 'test', [5, '~']],
            'pads_8_plus' => ['test++++', 'test', [8, '+']],
            'pads_4_two' => ['2022', '20', [4, '2']],
        ];
    }

    #[Test]
    #[DataProvider('padProvider')]
    public function it_pads_a_string(string $expected, string $input, array $params): void
    {
        $modified = $this->modify($input, $params);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value, array $params): string
    {
        return Modify::value($value)->strPadRight($params)->fetch();
    }
}
