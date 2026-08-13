<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\BasicDictionary;
use Statamic\Dictionaries\Countries;
use Statamic\Dictionaries\Dictionary;
use Statamic\Dictionaries\Item;
use Statamic\Exceptions\DictionaryNotFoundException;
use Statamic\Exceptions\UndefinedDictionaryException;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DictionaryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

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
            'url' => 'http://localhost/!/fieldtypes/dictionaries/countries',
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
    public function a_guest_can_request_options_from_a_dictionary_that_allows_public_access()
    {
        $this
            ->getJson($this->optionsUrl('countries'))
            ->assertOk()
            ->assertJsonStructure(['data' => [['key', 'value']]]);
    }

    #[Test]
    public function a_guest_cannot_request_options_from_a_dictionary_that_does_not_allow_public_access()
    {
        RestrictedDictionary::register();

        $this
            ->getJson($this->optionsUrl('restricted'))
            ->assertForbidden();
    }

    #[Test]
    public function a_cp_user_can_request_options_from_a_dictionary_that_does_not_allow_public_access()
    {
        RestrictedDictionary::register();

        $this
            ->actingAs(User::make()->makeSuper())
            ->getJson($this->optionsUrl('restricted'))
            ->assertOk()
            ->assertJsonStructure(['data' => [['key', 'value']]]);
    }

    #[Test]
    public function a_request_with_a_missing_dictionary_is_rejected()
    {
        $config = base64_encode(json_encode(['type' => 'dictionary']));

        $this
            ->getJson(route('statamic.dictionary-fieldtype', 'countries').'?config='.$config)
            ->assertNotFound();
    }

    #[Test]
    public function a_request_with_an_unknown_dictionary_is_rejected()
    {
        $config = base64_encode(json_encode(['type' => 'dictionary', 'dictionary' => 'not_a_real_dictionary']));

        $this
            ->getJson(route('statamic.dictionary-fieldtype', 'countries').'?config='.$config)
            ->assertNotFound();
    }

    private function optionsUrl(string $dictionary): string
    {
        $config = base64_encode(json_encode(['type' => 'dictionary', 'dictionary' => $dictionary]));

        return route('statamic.dictionary-fieldtype', $dictionary).'?config='.$config;
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
    public function it_includes_icons_in_preload_data_when_present()
    {
        IconDictionary::register();

        $field = (new Field('test', ['type' => 'dictionary', 'dictionary' => 'icon']));
        $field->setValue(['AL', 'AK']);

        $fieldtype = FieldtypeRepository::find('dictionary');
        $fieldtype->setField($field);

        $preload = $fieldtype->preload();

        $this->assertEquals([
            ['value' => 'AL', 'label' => 'Alabama', 'icon' => 'map-pin', 'invalid' => false],
            ['value' => 'AK', 'label' => 'Alaska', 'invalid' => false],
        ], $preload['selectedOptions']);
    }

    #[Test]
    public function the_options_api_returns_icons_when_present()
    {
        IconDictionary::register();

        $config = base64_encode(json_encode(['type' => 'dictionary', 'dictionary' => 'icon']));

        $this
            ->actingAs(User::make()->makeSuper())
            ->getJson(route('statamic.dictionary-fieldtype', 'icon').'?config='.$config)
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    ['key' => 'AL', 'value' => 'Alabama', 'icon' => 'map-pin'],
                    ['key' => 'AK', 'value' => 'Alaska'],
                ],
            ]);
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

class RestrictedDictionary extends Dictionary
{
    protected static $handle = 'restricted';

    public function options(?string $search = null): array
    {
        return ['foo' => 'Foo', 'bar' => 'Bar'];
    }

    public function get(string $key): ?Item
    {
        return new Item($key, $this->options()[$key], []);
    }
}

class IconDictionary extends BasicDictionary
{
    protected static $handle = 'icon';

    protected string $valueKey = 'abbr';
    protected string $labelKey = 'name';

    protected function getItems(): array
    {
        return [
            ['name' => 'Alabama', 'abbr' => 'AL', 'icon' => 'map-pin'],
            ['name' => 'Alaska', 'abbr' => 'AK'],
        ];
    }
}
