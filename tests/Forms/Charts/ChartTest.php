<?php

namespace Tests\Forms\Charts;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Charts\Lollipop;
use Statamic\Forms\Charts\Pie;
use Statamic\Forms\Charts\RankedOptions;
use Statamic\Forms\Charts\VerticalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Summary\FieldResponses;
use Tests\TestCase;

class ChartTest extends TestCase
{
    #[Test]
    public function it_counts_values_per_option()
    {
        $props = (new HorizontalBar)->props(
            $this->responses(['red', 'red', 'green']),
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
            $this->responses([['tea', 'coffee'], ['tea'], ['coffee']]),
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
            $this->responses([true, true, false]),
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
        $props = (new HorizontalBar)->props($this->responses(['cat']), collect([
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
    public function it_keeps_option_keys_that_are_loosely_equal_apart_when_truncating()
    {
        $props = (new Pie)->props($this->weightedValues(['0', 'a', 'b', '00', 'c']), $this->chartOptions(
            ['0' => 'Zero', 'a' => 'A', 'b' => 'B', '00' => 'Double Zero', 'c' => 'C']
        ));

        $this->assertEquals(['0', 'a', 'b', 'other'], array_column($props['items'], 'key'));
        $this->assertEquals(['00', 'c'], array_column($props['drilldown']['items'], 'key'));
    }

    #[Test]
    public function it_doesnt_drill_down_when_everything_fits()
    {
        $props = (new HorizontalBar)->props(
            $this->responses(['red', 'red', 'green']),
            $this->chartOptions(['red' => 'Red', 'green' => 'Green'])
        );

        $this->assertEquals(['items'], array_keys($props));
    }

    #[Test]
    public function other_inherits_a_shared_icon_from_truncated_options()
    {
        $options = collect(range(1, 8))->map(fn ($stars) => new ChartOption((string) $stars, icon: 'star-filled'));

        $props = (new HorizontalBar)->props($this->weightedValues(range(1, 8)), $options);

        $this->assertEquals('star-filled', collect($props['items'])->last()['icon']);
        $this->assertTrue(collect($props['items'])->last()['other']);
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
        $props = (new HorizontalBar)->props($this->responses(['Alice', 'Alice', 'Bob']));

        $this->assertEquals([
            ['key' => 'Alice', 'label' => 'Alice', 'count' => 2, 'percent' => 67],
            ['key' => 'Bob', 'label' => 'Bob', 'count' => 1, 'percent' => 33],
        ], $props['items']);
    }

    #[Test]
    public function it_sorts_unique_numeric_values_ascending()
    {
        $props = (new VerticalBar)->props($this->responses([3, 1, 3, 10]));

        $this->assertEquals([
            ['key' => '1', 'label' => '1', 'count' => 1, 'percent' => 25],
            ['key' => '3', 'label' => '3', 'count' => 2, 'percent' => 50],
            ['key' => '10', 'label' => '10', 'count' => 1, 'percent' => 25],
        ], $props['items']);
    }

    #[Test]
    public function it_bins_numeric_values_when_there_are_many_unique_ones()
    {
        $props = (new VerticalBar)->props($this->responses(range(1, 20)));

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
    public function it_bins_whole_numbers_across_a_wide_range_into_whole_number_ranges()
    {
        $props = (new VerticalBar)->props($this->responses([20, 35, 60, 90, 120, 150, 200, 250, 300, 400, 470]));

        $this->assertEquals([
            ['key' => '20-76', 'label' => '20–76', 'count' => 3, 'percent' => 27],
            ['key' => '77-133', 'label' => '77–133', 'count' => 2, 'percent' => 18],
            ['key' => '134-190', 'label' => '134–190', 'count' => 1, 'percent' => 9],
            ['key' => '191-247', 'label' => '191–247', 'count' => 1, 'percent' => 9],
            ['key' => '248-304', 'label' => '248–304', 'count' => 2, 'percent' => 18],
            ['key' => '305-361', 'label' => '305–361', 'count' => 0, 'percent' => 0],
            ['key' => '362-418', 'label' => '362–418', 'count' => 1, 'percent' => 9],
            ['key' => '419-470', 'label' => '419–470', 'count' => 1, 'percent' => 9],
        ], $props['items']);
    }

    #[Test]
    public function it_bins_decimal_values_into_decimal_ranges()
    {
        $props = (new VerticalBar)->props($this->responses([0.05, 0.1, 0.15, 0.2, 0.25, 0.3, 0.35, 0.4, 0.45, 0.5, 0.55]));

        $this->assertEquals([
            ['key' => '0.05-0.11', 'label' => '0.05–0.11', 'count' => 2, 'percent' => 18],
            ['key' => '0.12-0.18', 'label' => '0.12–0.18', 'count' => 1, 'percent' => 9],
            ['key' => '0.19-0.25', 'label' => '0.19–0.25', 'count' => 2, 'percent' => 18],
            ['key' => '0.26-0.32', 'label' => '0.26–0.32', 'count' => 1, 'percent' => 9],
            ['key' => '0.33-0.39', 'label' => '0.33–0.39', 'count' => 1, 'percent' => 9],
            ['key' => '0.40-0.46', 'label' => '0.40–0.46', 'count' => 2, 'percent' => 18],
            ['key' => '0.47-0.53', 'label' => '0.47–0.53', 'count' => 1, 'percent' => 9],
            ['key' => '0.54-0.55', 'label' => '0.54–0.55', 'count' => 1, 'percent' => 9],
        ], $props['items']);
    }

    #[Test]
    public function it_bins_decimal_values_either_side_of_zero()
    {
        $props = (new VerticalBar)->props($this->responses([-0.5, -0.4, -0.3, -0.2, -0.1, 0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6]));

        $this->assertEquals([
            ['key' => '-0.5--0.4', 'label' => '-0.5–-0.4', 'count' => 2, 'percent' => 17],
            ['key' => '-0.3--0.2', 'label' => '-0.3–-0.2', 'count' => 2, 'percent' => 17],
            ['key' => '-0.1-0.0', 'label' => '-0.1–0.0', 'count' => 2, 'percent' => 17],
            ['key' => '0.1-0.2', 'label' => '0.1–0.2', 'count' => 2, 'percent' => 17],
            ['key' => '0.3-0.4', 'label' => '0.3–0.4', 'count' => 2, 'percent' => 17],
            ['key' => '0.5-0.6', 'label' => '0.5–0.6', 'count' => 2, 'percent' => 17],
        ], $props['items']);
    }

    #[Test]
    public function it_bins_decimal_values_across_a_wide_range_into_whole_number_ranges()
    {
        $props = (new VerticalBar)->props($this->responses([1.5, 10.25, 20, 30.75, 40, 50.5, 60, 70.25, 80, 90.5, 100]));

        $this->assertEquals(['1–13', '14–26', '27–39', '40–52', '53–65', '66–78', '79–91', '92–100'], array_column($props['items'], 'label'));
        $this->assertEquals([2, 1, 1, 2, 1, 1, 2, 1], array_column($props['items'], 'count'));
    }

    #[Test]
    public function it_doesnt_bin_numeric_values_when_the_field_has_options()
    {
        $props = (new VerticalBar)->props(
            $this->responses(range(0, 10)),
            $this->chartOptions(collect(range(0, 10))->mapWithKeys(fn ($value) => [$value => (string) $value])->all())
        );

        $this->assertCount(11, $props['items']);
        $this->assertEquals('0', $props['items'][0]['key']);
    }

    #[Test]
    #[DataProvider('supportedValueTypesProvider')]
    public function it_declares_the_value_types_it_supports(string $chart, array $supports)
    {
        $this->assertEquals($supports, (new $chart)->supports());
    }

    public static function supportedValueTypesProvider()
    {
        $any = [FormValueType::Number, FormValueType::Boolean, FormValueType::Choice, FormValueType::Choices];

        return [
            'horizontal bar' => [HorizontalBar::class, $any],
            'vertical bar' => [VerticalBar::class, $any],
            'lollipop' => [Lollipop::class, $any],
            'pie' => [Pie::class, [FormValueType::Number, FormValueType::Boolean, FormValueType::Choice]],
            'ranked options' => [RankedOptions::class, [FormValueType::Ranking]],
        ];
    }

    #[Test]
    #[DataProvider('appliesToProvider')]
    public function it_applies_to_fields_whose_value_type_it_supports(string $chart, array $config, bool $applies)
    {
        $this->assertSame($applies, (new $chart)->appliesTo(new FormField('field', $config)));
    }

    public static function appliesToProvider()
    {
        return [
            'bar on checkboxes' => [HorizontalBar::class, ['type' => 'checkboxes'], true],
            'bar on ranking' => [HorizontalBar::class, ['type' => 'ranking'], false],
            'bar on short answer' => [HorizontalBar::class, ['type' => 'short_answer'], false],
            'pie on multi choice' => [Pie::class, ['type' => 'multi_choice'], true],
            'pie on toggle' => [Pie::class, ['type' => 'toggle'], true],
            'pie on checkboxes' => [Pie::class, ['type' => 'checkboxes'], false],
            'pie on single dropdown' => [Pie::class, ['type' => 'dropdown'], true],
            'pie on multiple dropdown' => [Pie::class, ['type' => 'dropdown', 'multiple' => true], false],
            'ranked options on ranking' => [RankedOptions::class, ['type' => 'ranking'], true],
            'ranked options on multi choice' => [RankedOptions::class, ['type' => 'multi_choice'], false],
        ];
    }

    private function weightedValues(array $keys): FieldResponses
    {
        return $this->responses(collect($keys)->flatMap(fn ($key, $index) => array_fill(0, count($keys) + 2 - $index, $key)));
    }

    private function chartOptions(array $options): Collection
    {
        return collect($options)->map(fn ($label, $key) => new ChartOption((string) $key, $label))->values();
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('field', ['type' => 'checkboxes']), $values);
    }
}
