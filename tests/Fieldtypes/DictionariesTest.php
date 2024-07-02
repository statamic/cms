<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Tests\TestCase;

class DictionariesTest extends TestCase
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
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries']));

        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $augment = $fieldtype->augment('USA');

        $this->assertEquals([
            'name' => 'United States',
            'iso3' => 'USA',
            'iso2' => 'US',
            'region' => 'Americas',
            'subregion' => 'Northern America',
            'emoji' => '🇺🇸',
        ], $augment);
    }

    #[Test]
    public function it_augments_multiple_options()
    {
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries', 'multiple' => true]));

        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $augment = $fieldtype->augment(['USA', 'GBR']);

        $this->assertEquals([
            [
                'name' => 'United States',
                'iso3' => 'USA',
                'iso2' => 'US',
                'region' => 'Americas',
                'subregion' => 'Northern America',
                'emoji' => '🇺🇸',
            ],
            [
                'name' => 'United Kingdom',
                'iso3' => 'GBR',
                'iso2' => 'GB',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'emoji' => '🇬🇧',
            ],
        ], $augment);
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
