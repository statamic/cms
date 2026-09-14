<?php

namespace Tests\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Insights\Average;
use Statamic\Forms\Insights\Checked;
use Statamic\Forms\Insights\MinMax;
use Statamic\Forms\Insights\StarRating;
use Tests\TestCase;

class InsightsTest extends TestCase
{
    #[Test]
    public function average_averages_numeric_values()
    {
        $this->assertEquals(
            ['average' => '2.3'],
            (new Average)->props(collect([1, 2, 4, 'nonsense']))
        );
    }

    #[Test]
    public function average_can_be_prefixed_and_rounded()
    {
        $this->assertEquals(
            ['average' => '2.33', 'prefix' => '£'],
            (new Average(prefix: '£', decimals: 2))->props(collect([1, 2, 4]))
        );
    }

    #[Test]
    public function min_max_uses_the_lowest_and_highest_values()
    {
        $this->assertEquals(
            ['min' => '4', 'max' => '44'],
            (new MinMax)->props(collect([26, 4, 44, 'nonsense']))
        );

        $this->assertEquals(
            ['min' => '5.00', 'max' => '50.00', 'prefix' => '£'],
            (new MinMax(prefix: '£', decimals: 2))->props(collect([5, 50, 30]))
        );
    }

    #[Test]
    public function checked_counts_truthy_values()
    {
        $this->assertEquals(
            ['count' => 2, 'percent' => 40],
            (new Checked)->props(collect([true, true, false, false, false]))
        );
    }

    #[Test]
    public function star_rating_includes_the_total()
    {
        $this->assertEquals(
            ['average' => 4.3, 'total' => 5],
            (new StarRating(total: 5))->props(collect([4, 4.5, 4.5]))
        );
    }

    #[Test]
    public function insights_handle_having_no_responses()
    {
        $this->assertEquals(['average' => '0.0'], (new Average)->props(collect()));
        $this->assertEquals(['min' => '0', 'max' => '0'], (new MinMax)->props(collect()));
        $this->assertEquals(['count' => 0, 'percent' => 0], (new Checked)->props(collect()));
        $this->assertEquals(['average' => 0.0, 'total' => 5], (new StarRating(total: 5))->props(collect()));
    }
}
