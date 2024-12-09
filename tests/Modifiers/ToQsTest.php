<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ToQsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_converts_to_query_string(): void
    {
        $input = [
            'mode' => 'plaid',
            'area' => [51, 52],
            'hat' => null,
            'transportation' => [
                'bike' => true,
                'delorian' => false,
            ],
        ];
        $modified = $this->modify(value($input));

        $expected = 'mode=plaid&area%5B0%5D=51&area%5B1%5D=52&transportation%5Bbike%5D=1&transportation%5Bdelorian%5D=0';
        $this->assertEquals($expected, $modified);
    }

    private function modify($value, $options = [])
    {
        return Modify::value($value)->toQs($options)->fetch();
    }
}
