<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\StarRating;
use Statamic\Forms\Insights\StarRating as StarRatingInsight;
use Tests\TestCase;

class StarRatingTest extends TestCase
{
    #[Test]
    public function it_returns_field_array_with_default_max_stars()
    {
        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
        ]));

        $this->assertEquals([
            'type' => 'star_rating',
            'max_stars' => 5,
            'allow_half_stars' => false,
            'min' => 1,
            'step' => 1,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config_and_normalizes_max_stars()
    {
        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
            'display' => 'How would you rate us?',
            'max_stars' => 3,
        ]));

        $this->assertEquals([
            'type' => 'star_rating',
            'max_stars' => 3,
            'allow_half_stars' => false,
            'min' => 1,
            'step' => 1,
            'display' => 'How would you rate us?',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_clamps_max_stars_to_a_sensible_range()
    {
        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
            'max_stars' => 25,
        ]));

        $this->assertEquals(10, $fieldtype->toFieldArray()['max_stars']);

        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
            'max_stars' => 0,
        ]));

        $this->assertEquals(1, $fieldtype->toFieldArray()['max_stars']);
    }

    #[Test]
    public function it_provides_half_star_step_when_enabled()
    {
        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
            'max_stars' => 5,
            'allow_half_stars' => true,
        ]));

        $array = $fieldtype->toFieldArray();

        $this->assertTrue($array['allow_half_stars']);
        $this->assertSame(0.5, $array['min']);
        $this->assertSame(0.5, $array['step']);
    }

    #[Test]
    public function it_defaults_to_a_bar_chart()
    {
        $this->assertEquals(HorizontalBar::class, (new StarRating)->defaultChart());
    }

    #[Test]
    public function it_returns_a_chart_option_for_each_star()
    {
        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
            'max_stars' => 3,
        ]));

        $options = $fieldtype->chartOptions(collect());

        $this->assertEquals(['3', '2', '1'], $options->map->key->all());
        $this->assertEquals(['star-filled', 'star-filled', 'star-filled'], $options->map->icon->all());
    }

    #[Test]
    public function it_returns_half_star_chart_options_when_enabled()
    {
        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
            'max_stars' => 3,
            'allow_half_stars' => true,
        ]));

        $this->assertEquals(['3', '2.5', '2', '1.5', '1', '0.5'], $fieldtype->chartOptions(collect())->map->key->all());
    }

    #[Test]
    public function it_returns_a_star_rating_insight()
    {
        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
            'max_stars' => 3,
        ]));

        $insights = $fieldtype->insights();

        $this->assertCount(1, $insights);
        $this->assertInstanceOf(StarRatingInsight::class, $insights[0]);
        $this->assertEquals(['average' => 2.0, 'total' => 3], $insights[0]->props(collect([1, 3])));
    }
}
