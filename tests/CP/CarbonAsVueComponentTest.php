<?php

namespace Tests\CP;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarbonAsVueComponentTest extends TestCase
{
    #[Test]
    #[DataProvider('macroProvider')]
    public function it_converts_to_a_vue_component($args, $expected)
    {
        $carbon = Carbon::parse('2022-12-25 10:32pm', 'America/New_York');

        $component = $carbon->asVueComponent(...$args);
        $this->assertInstanceOf(Htmlable::class, $component);
        $this->assertEquals($expected, $component->toHtml());
    }

    public static function macroProvider()
    {
        return [
            'just date' => [
                [],
                '<date-time of="2022-12-25T22:32:00-05:00"></date-time>',
            ],
            'options' => [
                [['foo' => 'bar']],
                '<date-time of="2022-12-25T22:32:00-05:00" :options=\'{"foo":"bar"}\'></date-time>',
            ],
        ];
    }
}
