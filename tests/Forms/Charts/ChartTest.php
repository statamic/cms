<?php

namespace Tests\Forms\Charts;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Charts\VerticalBar;
use Tests\TestCase;

class ChartTest extends TestCase
{
    #[Test]
    public function it_counts_values_per_option()
    {
        $props = (new HorizontalBar)->props(
            collect(['red', 'red', 'green']),
            $this->chartOptions(['red' => 'Red', 'green' => 'Green', 'blue' => 'Blue'])
        );

        $this->assertEquals([
            ['key' => 'red', 'label' => 'Red', 'count' => 2, 'percent' => 67],
            ['key' => 'green', 'label' => 'Green', 'count' => 1, 'percent' => 33],
            ['key' => 'blue', 'label' => 'Blue', 'count' => 0, 'percent' => 0],
        ], $props['items']);
    }

    #[Test]
    public function it_flattens_multi_value_fields_and_counts_each_selection()
    {
        $props = (new HorizontalBar)->props(
            collect([['tea', 'coffee'], ['tea'], ['coffee']]),
            $this->chartOptions(['tea' => 'Tea', 'coffee' => 'Coffee', 'water' => 'Water'])
        );

        $this->assertEquals([
            ['key' => 'tea', 'label' => 'Tea', 'count' => 2, 'percent' => 67],
            ['key' => 'coffee', 'label' => 'Coffee', 'count' => 2, 'percent' => 67],
            ['key' => 'water', 'label' => 'Water', 'count' => 0, 'percent' => 0],
        ], $props['items']);
    }

    #[Test]
    public function it_normalizes_boolean_values()
    {
        $props = (new HorizontalBar)->props(
            collect([true, true, false]),
            $this->chartOptions(['true' => 'Yes', 'false' => 'No'])
        );

        $this->assertEquals([
            ['key' => 'true', 'label' => 'Yes', 'count' => 2, 'percent' => 67],
            ['key' => 'false', 'label' => 'No', 'count' => 1, 'percent' => 33],
        ], $props['items']);
    }

    #[Test]
    public function it_passes_option_extras_through_to_items()
    {
        $props = (new HorizontalBar)->props(collect(['cat']), collect([
            new ChartOption('cat', 'Cat', icon: 'star-filled', image: '/cat.jpg', badge: 'A'),
        ]));

        $this->assertEquals([
            ['key' => 'cat', 'label' => 'Cat', 'count' => 1, 'percent' => 100, 'icon' => 'star-filled', 'image' => '/cat.jpg', 'badge' => 'A'],
        ], $props['items']);
    }

    #[Test]
    public function it_truncates_to_the_limit_and_lumps_the_rest_into_other()
    {
        $props = (new HorizontalBar)->props($this->weightedValues(range('a', 'h')), $this->chartOptions(
            collect(range('a', 'h'))->mapWithKeys(fn ($key) => [$key => strtoupper($key)])->all()
        ));

        $this->assertEquals([
            ['key' => 'a', 'label' => 'A', 'count' => 10, 'percent' => 19],
            ['key' => 'b', 'label' => 'B', 'count' => 9, 'percent' => 17],
            ['key' => 'c', 'label' => 'C', 'count' => 8, 'percent' => 15],
            ['key' => 'd', 'label' => 'D', 'count' => 7, 'percent' => 13],
            ['key' => 'other', 'label' => 'Other', 'count' => 18, 'percent' => 35, 'other' => true],
        ], $props['items']);
    }

    #[Test]
    public function it_doesnt_drill_down_when_everything_fits()
    {
        $props = (new HorizontalBar)->props(
            collect(['red', 'red', 'green']),
            $this->chartOptions(['red' => 'Red', 'green' => 'Green'])
        );

        $this->assertEquals(['items'], array_keys($props));
    }

    #[Test]
    public function it_drills_down_into_the_items_lumped_into_other()
    {
        $props = (new HorizontalBar)->props($this->weightedValues(range('a', 'h')), $this->chartOptions(
            collect(range('a', 'h'))->mapWithKeys(fn ($key) => [$key => strtoupper($key)])->all()
        ));

        $this->assertEquals([
            ['key' => 'e', 'label' => 'E', 'count' => 6, 'percent' => 12],
            ['key' => 'f', 'label' => 'F', 'count' => 5, 'percent' => 10],
            ['key' => 'g', 'label' => 'G', 'count' => 4, 'percent' => 8],
            ['key' => 'h', 'label' => 'H', 'count' => 3, 'percent' => 6],
        ], $props['drilldown']['items']);

        $this->assertEquals(4, $props['drilldown']['focusedIndex']);
    }

    #[Test]
    public function it_caps_the_drilldown_items_and_summarizes_the_rest()
    {
        $props = (new HorizontalBar)->props($this->weightedValues(range('a', 'l')), $this->chartOptions(
            collect(range('a', 'l'))->mapWithKeys(fn ($key) => [$key => strtoupper($key)])->all()
        ));

        $this->assertCount(5, $props['drilldown']['items']);
        $this->assertEquals('+4 more', collect($props['drilldown']['items'])->last()['label']);
    }

    #[Test]
    public function vertical_bars_truncate_unbounded_answers_too()
    {
        $props = (new VerticalBar)->props($this->weightedValues(range('a', 'n')), $this->chartOptions(
            collect(range('a', 'n'))->mapWithKeys(fn ($key) => [$key => strtoupper($key)])->all()
        ));

        $this->assertCount(12, $props['items']);
        $this->assertEquals('other', collect($props['items'])->last()['key']);
    }

    #[Test]
    public function it_counts_unique_values_when_there_are_no_options()
    {
        $props = (new HorizontalBar)->props(collect(['Alice', 'Alice', 'Bob']));

        $this->assertEquals([
            ['key' => 'Alice', 'label' => 'Alice', 'count' => 2, 'percent' => 67],
            ['key' => 'Bob', 'label' => 'Bob', 'count' => 1, 'percent' => 33],
        ], $props['items']);
    }

    #[Test]
    public function it_sorts_unique_numeric_values_ascending()
    {
        $props = (new VerticalBar)->props(collect([3, 1, 3, 10]));

        $this->assertEquals([
            ['key' => '1', 'label' => '1', 'count' => 1, 'percent' => 25],
            ['key' => '3', 'label' => '3', 'count' => 2, 'percent' => 50],
            ['key' => '10', 'label' => '10', 'count' => 1, 'percent' => 25],
        ], $props['items']);
    }

    #[Test]
    public function it_bins_numeric_values_when_there_are_many_unique_ones()
    {
        $props = (new VerticalBar)->props(collect(range(1, 20)));

        $this->assertEquals([
            ['key' => '1-3', 'label' => '1–3', 'count' => 3, 'percent' => 15],
            ['key' => '4-6', 'label' => '4–6', 'count' => 3, 'percent' => 15],
            ['key' => '7-9', 'label' => '7–9', 'count' => 3, 'percent' => 15],
            ['key' => '10-12', 'label' => '10–12', 'count' => 3, 'percent' => 15],
            ['key' => '13-15', 'label' => '13–15', 'count' => 3, 'percent' => 15],
            ['key' => '16-18', 'label' => '16–18', 'count' => 3, 'percent' => 15],
            ['key' => '19-20', 'label' => '19–20', 'count' => 2, 'percent' => 10],
        ], $props['items']);
    }

    #[Test]
    public function it_doesnt_bin_numeric_values_when_the_field_has_options()
    {
        $props = (new VerticalBar)->props(
            collect(range(0, 10)),
            $this->chartOptions(collect(range(0, 10))->mapWithKeys(fn ($value) => [$value => (string) $value])->all())
        );

        $this->assertCount(11, $props['items']);
        $this->assertEquals('0', $props['items'][0]['key']);
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
