<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\StarRating;
use Tests\TestCase;

class StarRatingTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
        ]));

        $this->assertEquals([
            'type' => 'star_rating',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new StarRating)->setField(new FormField('rating', [
            'type' => 'star_rating',
            'display' => 'How would you rate us?',
        ]));

        $this->assertEquals([
            'type' => 'star_rating',
            'display' => 'How would you rate us?',
        ], $fieldtype->toFieldArray());
    }
}
