<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class CamelizeTest extends TestCase
{
    public static function camelFarmProvider(): array
    {
        return [
            'it_camelize_underscores' => ['makeEverythingBetter', 'make_everything_better'],
            'it_camelize_dashes' => ['makeEverythingBetter', 'make-everything-better'],
            'it_capitalizes_letters_following_digits' => ['makeEverythingBetter', 'make-everything-better'],
            'it_trims_surrounding_spaces' => ['makeEverythingBetter', ' make_everything_better '],
            'it_removes_spaces' => ['makeEverythingBetter', ' make everything better'],
        ];
    }

    #[Test]
    #[DataProvider('camelFarmProvider')]
    public function it_camelizes_underscores($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->camelize()->fetch();
    }
}
