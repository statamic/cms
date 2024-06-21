<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SnakeTest extends TestCase
{
    public static function raidersOfTheLostArkProvider(): array
    {
        return [
            ['foo_bar_baz', 'foo bar baz'],
            ['lorem_ipsum_dolor_sit_amet,_consectetur_adipiscing_elit,_sed_do_eiusmod_tempor_incididunt_ut_labore_et_dolore_magna_aliqua.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'],
        ];
    }

    #[Test]
    #[DataProvider('raidersOfTheLostArkProvider')]
    public function it_converts_a_string_into_snake_case($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->snake()->fetch();
    }
}
