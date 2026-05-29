<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\ImageChoice;
use Tests\TestCase;

class ImageChoiceTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new ImageChoice)->setField(new FormField('favorite_color', [
            'type' => 'image_choice',
        ]));

        $this->assertEquals([
            'type' => 'image_choice',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new ImageChoice)->setField(new FormField('favorite_color', [
            'type' => 'image_choice',
            'display' => 'Choose your favorite',
        ]));

        $this->assertEquals([
            'type' => 'image_choice',
            'display' => 'Choose your favorite',
        ], $fieldtype->toFieldArray());
    }
}
