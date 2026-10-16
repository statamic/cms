<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Dictionary as Dictionaries;
use Statamic\Forms\Charts\Lollipop;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Fieldtypes\Dictionary;
use Statamic\Forms\Summary\FieldResponses;
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
    public function it_stores_a_single_choice_when_limited_to_one_item()
    {
        $fieldtype = (new Dictionary)->setField(new FormField('field', ['type' => 'dictionary', 'max_items' => 1]));

        $this->assertSame(FormValueType::Choice, $fieldtype->valueType());
    }

    #[Test]
    public function it_stores_a_single_choice_when_limited_to_one_item_as_a_string()
    {
        $fieldtype = (new Dictionary)->setField(new FormField('field', ['type' => 'dictionary', 'max_items' => '1']));

        $this->assertSame(FormValueType::Choice, $fieldtype->valueType());
    }

    #[Test]
    public function it_stores_multiple_choices_when_allowing_more_than_one_item()
    {
        $fieldtype = (new Dictionary)->setField(new FormField('field', ['type' => 'dictionary', 'max_items' => 3]));

        $this->assertSame(FormValueType::Choices, $fieldtype->valueType());
    }

    #[Test]
    public function it_stores_multiple_choices_when_unlimited()
    {
        $fieldtype = (new Dictionary)->setField(new FormField('field', ['type' => 'dictionary', 'max_items' => null]));

        $this->assertSame(FormValueType::Choices, $fieldtype->valueType());
    }

    #[Test]
    public function it_derives_labelled_chart_options_from_the_submitted_values()
    {
        $fieldtype = (new Dictionary)->setField(new FormField('country', [
            'type' => 'dictionary',
            'dictionary' => 'countries',
        ]));

        $options = $fieldtype->chartOptions($this->responses(['USA', 'GBR', 'GBR']));

        $this->assertEquals(['GBR', 'USA'], $options->map->key->all());
        $this->assertEquals([
            Dictionaries::find('countries')->get('GBR')->label(),
            Dictionaries::find('countries')->get('USA')->label(),
        ], $options->map->label->all());
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('field', ['type' => 'dictionary']), $values);
    }
}
