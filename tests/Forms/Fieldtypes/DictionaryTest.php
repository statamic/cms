<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Dictionary;
use Tests\TestCase;

class DictionaryTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Dictionary)->setField(new FormField('country', [
            'type' => 'dictionary',
            'dictionary' => 'countries',
            'placeholder' => 'Select a country',
            'max_items' => 3,
        ]));

        $this->assertEquals([
            'type' => 'dictionary',
            'dictionary' => 'countries',
            'placeholder' => 'Select a country',
            'max_items' => 3,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Dictionary)->setField(new FormField('country', [
            'type' => 'dictionary',
            'dictionary' => 'countries',
            'placeholder' => 'Select a country',
            'max_items' => 1,
            'default' => 'USA',
        ]));

        $this->assertEquals([
            'type' => 'dictionary',
            'dictionary' => 'countries',
            'placeholder' => 'Select a country',
            'max_items' => 1,
            'default' => 'USA',
        ], $fieldtype->toFieldArray());
    }
}
