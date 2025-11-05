<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Countries;
use Statamic\Dictionaries\Item;
use Statamic\Exceptions\DictionaryNotFoundException;
use Statamic\Exceptions\UndefinedDictionaryException;
use Statamic\Fields\Field;
use Tests\TestCase;

class DictionaryTest extends TestCase
{
    #[Test]
    #[DataProvider('dictionaryConfigProvider')]
    public function it_gets_the_dictionary($dictionaryConfig, $expectedConfig)
    {
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => $dictionaryConfig]));
        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $dictionary = $fieldtype->dictionary();
        $this->assertInstanceOf(Countries::class, $dictionary);
        $this->assertEquals($expectedConfig, $dictionary->config());
    }

    public static function dictionaryConfigProvider()
    {
        return [
            'string' => [
                'countries',
                [],
            ],
            'array' => [
                ['type' => 'countries', 'foo' => 'bar', 'baz' => 'qux'],
                ['foo' => 'bar', 'baz' => 'qux'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('undefinedDictionaryConfigProvider')]
    public function it_throw_exception_when_dictionary_is_undefined($dictionaryConfig)
    {
        $this->expectException(UndefinedDictionaryException::class);
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => $dictionaryConfig]));
        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);
        $fieldtype->dictionary();
    }

    public static function undefinedDictionaryConfigProvider()
    {
        return [
            'string' => [null],
            'array' => [['foo' => 'bar']],
        ];
    }

    #[Test]
    #[DataProvider('invalidDictionaryConfigProvider')]
    public function it_throws_exception_when_invalid_dictionary_is_defined($dictionaryConfig)
    {
        $this->expectException(DictionaryNotFoundException::class);
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => $dictionaryConfig]));
        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);
        $fieldtype->dictionary();
    }

    public static function invalidDictionaryConfigProvider()
    {
        return [
            'string' => ['invalid'],
            'array' => [['type' => 'invalid']],
        ];
    }

    #[Test]
    public function it_returns_preload_data()
    {
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries']));
        $field->setValue(['USA', 'AUS', 'CAN', 'BLA', 'DEU', 'GBR']);

        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $preload = $fieldtype->preload();

        $this->assertArraySubset([
            'url' => 'http://localhost/cp/fieldtypes/dictionaries/countries',
            'selectedOptions' => [
                ['value' => 'USA', 'label' => '🇺🇸 United States', 'invalid' => false],
                ['value' => 'AUS', 'label' => '🇦🇺 Australia', 'invalid' => false],
                ['value' => 'CAN', 'label' => '🇨🇦 Canada', 'invalid' => false],
                ['value' => 'BLA', 'label' => 'BLA', 'invalid' => true],
                ['value' => 'DEU', 'label' => '🇩🇪 Germany', 'invalid' => false],
                ['value' => 'GBR', 'label' => '🇬🇧 United Kingdom', 'invalid' => false],
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
    public function it_translates_augmented_data()
    {
        app()->setLocale('de');
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries']));

        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $augment = $fieldtype->augment(['USA', 'GBR']);

        $this->assertEquals([
            [
                'name' => 'Vereinigte Staaten',
                'key' => 'USA',
                'iso3' => 'USA',
                'iso2' => 'US',
                'region' => 'Amerika',
                'subregion' => 'Nordamerika',
                'emoji' => '🇺🇸',
                'value' => 'USA',
                'label' => '🇺🇸 Vereinigte Staaten',
            ],
            [
                'name' => 'Vereinigtes Königreich',
                'key' => 'GBR',
                'iso3' => 'GBR',
                'iso2' => 'GB',
                'region' => 'Europa',
                'subregion' => 'Nordeuropa',
                'emoji' => '🇬🇧',
                'value' => 'GBR',
                'label' => '🇬🇧 Vereinigtes Königreich',
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
    public function invalid_value_augments_to_null()
    {
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries', 'max_items' => 1]));
        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $augmented = $fieldtype->augment('invalid');
        $this->assertInstanceOf(Item::class, $augmented);
        $this->assertNull($augmented->value());
        $this->assertNull($augmented->label());
    }

    #[Test]
    public function it_filters_out_invalid_values_when_augmenting_multiple()
    {
        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'countries']));

        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $augment = $fieldtype->augment(['USA', 'Invalid']);

        $this->assertCount(1, $augment);
        $this->assertEveryItemIsInstanceOf(Item::class, $augment);
        $this->assertEquals([
            [
                'name' => 'United States',
                'key' => 'USA',
                'iso3' => 'USA',
                'iso2' => 'US',
                'region' => 'Americas',
                'subregion' => 'Northern America',
                'emoji' => '🇺🇸',
                'value' => 'USA',
                'label' => '🇺🇸 United States',
            ],
        ], collect($augment)->toArray());
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
