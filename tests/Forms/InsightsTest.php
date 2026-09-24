<?php

namespace Tests\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
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
            (new Average)->setConfig(['prefix' => '£', 'decimals' => 2])->props($this->responses([1, 2, 4]))
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
            (new MinMax)->setConfig(['prefix' => '£', 'decimals' => 2])->props($this->responses([5, 50, 30]))
        );
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
        $this->assertSame(['decimals' => 1], (new Average)->config());
        $this->assertSame(1, (new Average)->config('decimals'));
        $this->assertNull((new Average)->config('prefix'));
        $this->assertSame('fallback', (new Average)->config('prefix', 'fallback'));

        $average = (new Average)->setConfig(['decimals' => 3, 'suffix' => '%']);

        $this->assertSame(['decimals' => 3, 'suffix' => '%'], $average->config());
        $this->assertSame(3, $average->config('decimals'));
    }

    #[Test]
    public function config_values_are_cast_defensively()
    {
        $this->assertEquals(
            ['average' => '2', 'suffix' => '%'],
            (new Average)->setConfig(['decimals' => 'nonsense', 'suffix' => '%'])->props($this->responses([1, 2, 4]))
        );

        $this->assertEquals(
            ['min' => '1.0', 'max' => '4.0'],
            (new MinMax)->setConfig(['decimals' => '1'])->props($this->responses([1, 2, 4]))
        );

        $this->assertEquals(
            ['average' => 2.3, 'total' => 0],
            (new StarRating)->props($this->responses([1, 2, 4]))
        );
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('field', ['type' => 'number']), $values);
    }
}
