<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Item;
use Statamic\Fields\Field;
use Tests\TestCase;

class DictionaryTest extends TestCase
{
    #[Test]
    public function it_returns_preload_data()
    {
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries']));
        $field->setValue(['USA', 'AUS', 'CAN', 'DEU', 'GBR']);

        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $preload = $fieldtype->preload();

        $this->assertArraySubset([
            'url' => 'http://localhost/cp/fieldtypes/dictionaries/countries',
            'selectedOptions' => [
                ['value' => 'AUS', 'label' => '🇦🇺 Australia'],
                ['value' => 'CAN', 'label' => '🇨🇦 Canada'],
                ['value' => 'DEU', 'label' => '🇩🇪 Germany'],
                ['value' => 'GBR', 'label' => '🇬🇧 United Kingdom'],
                ['value' => 'USA', 'label' => '🇺🇸 United States'],
            ],
        ], $preload);
    }

    #[Test]
    public function it_augments_a_single_option()
    {
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries', 'max_items' => 1]));

        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $augmented = $fieldtype->augment('USA');
        $this->assertInstanceOf(Item::class, $augmented);
        $this->assertEquals('USA', $augmented->value());
        $this->assertEquals('🇺🇸 United States', $augmented->label());
        $this->assertEquals([
            'key' => 'USA',
            'name' => 'United States',
            'iso3' => 'USA',
            'iso2' => 'US',
            'region' => 'Americas',
            'subregion' => 'Northern America',
            'emoji' => '🇺🇸',
            'value' => 'USA',
            'label' => '🇺🇸 United States',
        ], $augmented->toArray());

        $augmented = $fieldtype->augment(null);
        $this->assertInstanceOf(Item::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());
    }

    #[Test]
    public function it_augments_multiple_options()
    {
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries']));

        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $augment = $fieldtype->augment(['USA', 'GBR']);

        $this->assertEveryItemIsInstanceOf(Item::class, $augment);

        $this->assertEquals([
            [
                'name' => 'United States',
                'label' => 'United States',
                'key' => 'USA',
                'iso3' => 'USA',
                'iso2' => 'US',
                'region' => 'Americas',
                'subregion' => 'Northern America',
                'emoji' => '🇺🇸',
                'value' => 'USA',
                'label' => '🇺🇸 United States',
            ],
            [
                'name' => 'United Kingdom',
                'label' => 'United Kingdom',
                'key' => 'GBR',
                'iso3' => 'GBR',
                'iso2' => 'GB',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'emoji' => '🇬🇧',
                'value' => 'GBR',
                'label' => '🇬🇧 United Kingdom',
            ],
        ], collect($augment)->toArray());
    }

    #[Test]
    public function it_augments_to_empty_array_when_null_and_configured_for_multiple()
    {
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries']));
        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $this->assertEquals([], $fieldtype->augment(null));
    }

    #[Test]
    public function it_returns_extra_renderable_field_data()
    {
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries']));
        $field->setValue(['USA', 'AUS', 'CAN', 'DEU', 'GBR']);

        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $extraRenderableFieldData = $fieldtype->extraRenderableFieldData();

        $this->assertArraySubset([
            'options' => [
                'AUS' => '🇦🇺 Australia',
                'CAN' => '🇨🇦 Canada',
                'DEU' => '🇩🇪 Germany',
                'GBR' => '🇬🇧 United Kingdom',
                'USA' => '🇺🇸 United States',
            ],
        ], $extraRenderableFieldData);
    }
}
