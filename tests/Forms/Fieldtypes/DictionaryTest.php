<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Dictionary as Dictionaries;
use Statamic\Forms\Charts\Lollipop;
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

    #[Test]
    public function it_defaults_to_a_lollipop_chart()
    {
        $this->assertEquals(Lollipop::class, (new Dictionary)->defaultChart());
    }

    #[Test]
    public function it_derives_labelled_chart_options_from_the_submitted_values()
    {
        $fieldtype = (new Dictionary)->setField(new FormField('country', [
            'type' => 'dictionary',
            'dictionary' => 'countries',
        ]));

        $options = $fieldtype->chartOptions(collect(['USA', 'GBR', 'GBR']));

        $this->assertEquals(['GBR', 'USA'], $options->map->key->all());
        $this->assertEquals([
            Dictionaries::find('countries')->get('GBR')->label(),
            Dictionaries::find('countries')->get('USA')->label(),
        ], $options->map->label->all());
    }
}
