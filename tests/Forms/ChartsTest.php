<?php

namespace Tests\Forms;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Charts\Pie;
use Statamic\Forms\Charts\RankedOptions;
use Statamic\Forms\Charts\VerticalBar;
use Tests\TestCase;

class ChartsTest extends TestCase
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

        $this->assertArrayNotHasKey('other_items', $props);
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
        $values = collect([
            ...array_fill(0, 5, 'a'),
            ...array_fill(0, 4, 'b'),
            ...array_fill(0, 3, 'c'),
            ...array_fill(0, 2, 'd'),
            'e',
        ]);

        $props = (new Pie)->props($values, $this->chartOptions(
            collect(range('a', 'f'))->mapWithKeys(fn ($key) => [$key => strtoupper($key)])->all()
        ));

        $this->assertEquals([
            ['key' => 'a', 'label' => 'A', 'count' => 5, 'percent' => 33],
            ['key' => 'b', 'label' => 'B', 'count' => 4, 'percent' => 27],
            ['key' => 'c', 'label' => 'C', 'count' => 3, 'percent' => 20],
            ['key' => 'other', 'label' => 'Other', 'count' => 3, 'percent' => 20, 'other' => true],
        ], $props['items']);

        $this->assertEquals([
            ['key' => 'd', 'label' => 'D', 'count' => 2, 'percent' => 13],
            ['key' => 'e', 'label' => 'E', 'count' => 1, 'percent' => 7],
            ['key' => 'f', 'label' => 'F', 'count' => 0, 'percent' => 0],
        ], $props['other_items']);
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

    #[Test]
    public function it_ranks_options_by_average_position()
    {
        $props = (new RankedOptions)->props(
            collect([
                ['summer', 'spring', 'winter'],
                ['summer', 'winter', 'spring'],
                ['spring', 'summer', 'winter'],
            ]),
            $this->chartOptions(['spring' => 'Spring', 'summer' => 'Summer', 'winter' => 'Winter'])
        );

        $this->assertEquals([
            ['key' => 'summer', 'label' => 'Summer', 'rank' => 1, 'count' => 2, 'percent' => 89],
            ['key' => 'spring', 'label' => 'Spring', 'rank' => 2, 'count' => 1, 'percent' => 67],
            ['key' => 'winter', 'label' => 'Winter', 'rank' => 3, 'count' => 0, 'percent' => 44],
        ], $props['items']);
    }

    private function chartOptions(array $options): Collection
    {
        return collect($options)->map(fn ($label, $key) => new ChartOption((string) $key, $label))->values();
    }
}
