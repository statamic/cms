<?php

namespace Tests\Forms;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Insights\Average;
use Statamic\Forms\Insights\Checked;
use Statamic\Forms\Insights\MinMax;
use Statamic\Forms\Insights\StarRating;
use Statamic\Forms\Summary\FieldResponses;
use Tests\TestCase;

class InsightsTest extends TestCase
{
    #[Test]
    public function average_averages_numeric_values()
    {
        $this->assertEquals(
            ['average' => '2.3'],
            (new Average)->props($this->responses([1, 2, 4, 'nonsense']))
        );
    }

    #[Test]
    public function average_can_be_prefixed_and_rounded()
    {
        $this->assertEquals(
            ['average' => '2.33', 'prefix' => '£'],
            (new Average)->setConfig(['prefix' => '£', 'precision' => 2])->props($this->responses([1, 2, 4]))
        );
    }

    #[Test]
    public function min_max_uses_the_lowest_and_highest_values()
    {
        $this->assertEquals(
            ['min' => '4', 'max' => '44'],
            (new MinMax)->props($this->responses([26, 4, 44, 'nonsense']))
        );

        $this->assertEquals(
            ['min' => '5.00', 'max' => '50.00', 'prefix' => '£'],
            (new MinMax)->setConfig(['prefix' => '£', 'precision' => 2])->props($this->responses([5, 50, 30]))
        );
    }

    #[Test]
    #[DataProvider('precisionProvider')]
    public function precision_decides_how_many_decimals_each_insight_shows(array $config, array $minMax, array $average)
    {
        $responses = $this->responses([18, 24, 22]);

        $this->assertEquals($minMax, (new MinMax)->setConfig($config)->props($responses));
        $this->assertEquals($average, (new Average)->setConfig($config)->props($responses));
    }

    public static function precisionProvider()
    {
        return [
            'whole numbers' => [['precision' => 0], ['min' => '18', 'max' => '24'], ['average' => '21.3']],
            'one decimal' => [['precision' => 1], ['min' => '18.0', 'max' => '24.0'], ['average' => '21.3']],
            'currency' => [['precision' => 2], ['min' => '18.00', 'max' => '24.00'], ['average' => '21.33']],
        ];
    }

    #[Test]
    public function checked_counts_truthy_values()
    {
        $this->assertEquals(
            ['count' => 2, 'percent' => 40],
            (new Checked)->props($this->responses([true, true, false, false, false]))
        );
    }

    #[Test]
    public function star_rating_includes_the_total()
    {
        $this->assertEquals(
            ['average' => 4.3, 'total' => 5],
            (new StarRating)->setConfig(['total' => 5])->props($this->responses([4, 4.5, 4.5]))
        );
    }

    #[Test]
    public function insights_handle_having_no_responses()
    {
        $this->assertEquals(['average' => '0.0'], (new Average)->props($this->responses([])));
        $this->assertEquals(['min' => '0', 'max' => '0'], (new MinMax)->props($this->responses([])));
        $this->assertEquals(['count' => 0, 'percent' => 0], (new Checked)->props($this->responses([])));
        $this->assertEquals(['average' => 0.0, 'total' => 5], (new StarRating)->setConfig(['total' => 5])->props($this->responses([])));
    }

    #[Test]
    public function config_merges_defaults_with_the_given_config()
    {
        $this->assertSame(['precision' => 0], (new Average)->config());
        $this->assertSame(0, (new Average)->config('precision'));
        $this->assertNull((new Average)->config('prefix'));
        $this->assertSame('fallback', (new Average)->config('prefix', 'fallback'));

        $average = (new Average)->setConfig(['precision' => 3, 'suffix' => '%']);

        $this->assertSame(['precision' => 3, 'suffix' => '%'], $average->config());
        $this->assertSame(3, $average->config('precision'));
    }

    #[Test]
    public function config_values_are_cast_defensively()
    {
        $this->assertEquals(
            ['average' => '2.3', 'suffix' => '%'],
            (new Average)->setConfig(['precision' => 'nonsense', 'suffix' => '%'])->props($this->responses([1, 2, 4]))
        );

        $this->assertEquals(
            ['min' => '1.0', 'max' => '4.0'],
            (new MinMax)->setConfig(['precision' => '1'])->props($this->responses([1, 2, 4]))
        );

        $this->assertEquals(
            ['average' => 2.3, 'total' => 0],
            (new StarRating)->props($this->responses([1, 2, 4]))
        );
    }

    #[Test]
    #[DataProvider('supportedValueTypesProvider')]
    public function it_declares_the_value_types_it_supports(string $insight, array $supports)
    {
        $this->assertEquals($supports, (new $insight)->supports());
    }

    public static function supportedValueTypesProvider()
    {
        return [
            'average' => [Average::class, [FormValueType::Number]],
            'min max' => [MinMax::class, [FormValueType::Number]],
            'star rating' => [StarRating::class, [FormValueType::Number]],
            'checked' => [Checked::class, [FormValueType::Boolean]],
        ];
    }

    #[Test]
    #[DataProvider('appliesToProvider')]
    public function it_applies_to_fields_whose_value_type_it_supports(string $insight, array $config, bool $applies)
    {
        $this->assertSame($applies, (new $insight)->appliesTo(new FormField('field', $config)));
    }

    public static function appliesToProvider()
    {
        return [
            'average on number' => [Average::class, ['type' => 'number'], true],
            'average on opinion scale' => [Average::class, ['type' => 'opinion_scale'], true],
            'average on toggle' => [Average::class, ['type' => 'toggle'], false],
            'min max on currency' => [MinMax::class, ['type' => 'currency', 'currency' => 'GBP'], true],
            'min max on multi choice' => [MinMax::class, ['type' => 'multi_choice'], false],
            'checked on toggle' => [Checked::class, ['type' => 'toggle'], true],
            'checked on yes no' => [Checked::class, ['type' => 'yes_no'], false],
            'star rating on star rating' => [StarRating::class, ['type' => 'star_rating'], true],
            'star rating on number' => [StarRating::class, ['type' => 'number'], false],
        ];
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('field', ['type' => 'number']), $values);
    }
}
