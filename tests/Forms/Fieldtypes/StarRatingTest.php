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
            'stars' => [1, 2, 3, 4, 5],
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
            'stars' => [1, 2, 3],
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
}
