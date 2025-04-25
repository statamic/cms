<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Dictionaries\Dictionary;
use Statamic\Dictionaries\Item;
use Statamic\Fields\Field;
use Tests\TestCase;

class DictionaryFieldsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        FakeDictionary::register();
    }

    #[Test]
    public function it_returns_dictionary_fields_in_preload()
    {
        $fieldtype = FieldtypeRepository::find('dictionary_fields');

        $preload = $fieldtype->preload();

        $this->assertArraySubset([
            'type' => [
                'fields' => [
                    ['handle' => 'type', 'type' => 'select'],
                ],
                'meta' => [
                    'type' => [
                        'options' => [
                            ['value' => 'countries', 'label' => 'Countries'],
                            ['value' => 'currencies', 'label' => 'Currencies'],
                            ['value' => 'file', 'label' => 'File'],
                            ['value' => 'timezones', 'label' => 'Timezones'],
                            ['value' => 'fake_dictionary', 'label' => 'Fake Dictionary'],
                        ],
                    ],
                ],
            ],
        ], $preload);

        $this->assertArraySubset([
            'fake_dictionary' => [
                'fields' => [
                    ['handle' => 'category',  'type' => 'select'],
                ],
                'meta' => [
                    'category' => [
                        'options' => [],
                    ],
                ],
                'defaults' => ['category' => null],
            ],
        ], $preload['dictionaries']);
    }

    #[Test]
    public function it_pre_processes_dictionary_fields()
    {
        $fieldtype = FieldtypeRepository::find('dictionary_fields');

        $preProcess = $fieldtype->preProcess([
            'type' => 'fake_dictionary',
            'category' => 'foo',
        ]);

        $this->assertEquals([
            'type' => 'fake_dictionary',
            'category' => 'foo',
        ], $preProcess);
    }

    #[Test]
    public function it_pre_processes_dictionary_fields_saved_as_a_string()
    {
        $fieldtype = FieldtypeRepository::find('dictionary_fields');

        $preProcess = $fieldtype->preProcess('fake_dictionary');

        $this->assertEquals([
            'type' => 'fake_dictionary',
            'category' => null,
        ], $preProcess);
    }

    #[Test]
    public function it_processes_dictionary_fields()
    {
        $fieldtype = FieldtypeRepository::find('dictionary_fields');

        $process = $fieldtype->process([
            'type' => 'fake_dictionary',
            'category' => 'foo',
        ]);

        $this->assertEquals([
            'type' => 'fake_dictionary',
            'category' => 'foo',
        ], $process);
    }

    #[Test]
    public function it_processes_dictionary_fields_into_a_string_when_dictionary_has_no_config_values()
    {
        $fieldtype = FieldtypeRepository::find('dictionary_fields');

        $process = $fieldtype->process([
            'type' => 'fake_dictionary',
        ]);

        $this->assertEquals('fake_dictionary', $process);
    }

    #[Test]
    public function it_returns_validation_rules()
    {
        $field = (new Field('test', ['type' => 'dictionary_fields']))->setValue(['type' => 'fake_dictionary']);

        $fieldtype = FieldtypeRepository::find('dictionary_fields');
        $fieldtype->setField($field);

        $extraRules = $fieldtype->extraRules();

        $this->assertEquals([
            'test.category' => ['required'],
        ], $extraRules);
    }

    #[Test]
    public function it_returns_validation_rules_when_no_dictionary_is_selected()
    {
        $field = (new Field('test', ['type' => 'dictionary_fields']));

        $fieldtype = FieldtypeRepository::find('dictionary_fields');
        $fieldtype->setField($field);

        $extraRules = $fieldtype->extraRules();

        $this->assertEquals([
            'test.type' => ['required'],
        ], $extraRules);
    }
}

class FakeDictionary extends Dictionary
{
    public function options(?string $search = null): array
    {
        return [];
    }

    public function get(string $key): ?Item
    {
        return [];
    }

    protected function fieldItems()
    {
        return [
            'category' => [
                'type' => 'select',
                'validate' => 'required',
            ],
        ];
    }
}
