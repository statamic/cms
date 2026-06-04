<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\StarRating;
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
            'step' => 1,
            'stars' => [1, 2, 3, 4, 5],
            'rating_values' => [1, 2, 3, 4, 5],
            'star_inputs' => [
                ['star' => 1, 'half_value' => null, 'full_value' => 1],
                ['star' => 2, 'half_value' => null, 'full_value' => 2],
                ['star' => 3, 'half_value' => null, 'full_value' => 3],
                ['star' => 4, 'half_value' => null, 'full_value' => 4],
                ['star' => 5, 'half_value' => null, 'full_value' => 5],
            ],
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
            'step' => 1,
            'stars' => [1, 2, 3],
            'rating_values' => [1, 2, 3],
            'star_inputs' => [
                ['star' => 1, 'half_value' => null, 'full_value' => 1],
                ['star' => 2, 'half_value' => null, 'full_value' => 2],
                ['star' => 3, 'half_value' => null, 'full_value' => 3],
            ],
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
        $this->assertCount(10, $fieldtype->toFieldArray()['stars']);
    }

    #[Test]
    public function it_provides_half_star_rating_values_when_enabled()
    {
        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
            'max_stars' => 3,
            'allow_half_stars' => true,
        ]));

        $array = $fieldtype->toFieldArray();

        $this->assertTrue($array['allow_half_stars']);
        $this->assertSame(0.5, $array['step']);
        $this->assertEquals([0.5, 1, 1.5, 2, 2.5, 3], $array['rating_values']);
        $this->assertEquals([
            ['star' => 1, 'half_value' => 0.5, 'full_value' => 1],
            ['star' => 2, 'half_value' => 1.5, 'full_value' => 2],
            ['star' => 3, 'half_value' => 2.5, 'full_value' => 3],
        ], $array['star_inputs']);
    }
}
