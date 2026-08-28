<?php

namespace Tests\Forms\Charts;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\Pie;
use Tests\TestCase;

class PieTest extends TestCase
{
    #[Test]
    public function it_doesnt_drill_down_when_everything_fits()
    {
        $props = (new Pie)->props(
            collect(['red', 'red', 'green']),
            $this->chartOptions(['red' => 'Red', 'green' => 'Green'])
        );

        $this->assertEquals(['items'], array_keys($props));
    }

    #[Test]
    public function it_drills_down_into_the_items_that_didnt_fit()
    {
        $props = (new Pie)->props($this->weightedValues(range('a', 'f')), $this->chartOptions(
            collect(range('a', 'f'))->mapWithKeys(fn ($key) => [$key => strtoupper($key)])->all()
        ));

        $this->assertEquals([
            ['key' => 'd', 'label' => 'D', 'count' => 5, 'percent' => 15],
            ['key' => 'e', 'label' => 'E', 'count' => 4, 'percent' => 12],
            ['key' => 'f', 'label' => 'F', 'count' => 3, 'percent' => 9],
        ], $props['drilldown']['items']);
    }

    #[Test]
    public function it_keeps_the_pie_intact_while_drilled_in()
    {
        $props = (new Pie)->props($this->weightedValues(range('a', 'f')), $this->chartOptions(
            collect(range('a', 'f'))->mapWithKeys(fn ($key) => [$key => strtoupper($key)])->all()
        ));

        $this->assertEquals($props['items'], $props['drilldown']['segments']);
        $this->assertEquals(3, $props['drilldown']['focusedIndex']);
    }

    #[Test]
    public function it_caps_the_drilldown_items_and_summarizes_the_rest()
    {
        $props = (new Pie)->props($this->weightedValues(range('a', 'h')), $this->chartOptions(
            collect(range('a', 'h'))->mapWithKeys(fn ($key) => [$key => strtoupper($key)])->all()
        ));

        $this->assertEquals([
            ['key' => 'd', 'label' => 'D', 'count' => 7, 'percent' => 13],
            ['key' => 'e', 'label' => 'E', 'count' => 6, 'percent' => 12],
            ['key' => 'f', 'label' => 'F', 'count' => 5, 'percent' => 10],
            ['key' => 'more', 'label' => '+2 more', 'count' => 7, 'percent' => 13],
        ], $props['drilldown']['items']);
    }

    private function weightedValues(array $keys): Collection
    {
        return collect($keys)->flatMap(fn ($key, $index) => array_fill(0, count($keys) + 2 - $index, $key));
    }

    private function chartOptions(array $options): Collection
    {
        return collect($options)->map(fn ($label, $key) => new ChartOption((string) $key, $label))->values();
    }
}
