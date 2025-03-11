<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Blueprint;
use Statamic\Fields\ConfigFields;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Tests\TestCase;

class FieldtypeTest extends TestCase
{
    #[Test]
    public function it_gets_the_field()
    {
        $fieldtype = new TestFieldtype;
        $field = new Field('test', ['foo' => 'bar']);

        $this->assertNull($fieldtype->field());

        $return = $fieldtype->setField($field);

        $this->assertEquals($fieldtype, $return);
        $this->assertEquals($field, $fieldtype->field());
    }

    #[Test]
    public function the_handle_is_snake_cased_from_the_class_by_default()
    {
        $this->assertEquals(
            'test_multi_word',
            (new TestMultiWordFieldtype)->handle()
        );

        $this->assertEquals(
            'test_multi_word_with_no_fieldtype_suffix',
            (new TestMultiWordWithNoFieldtypeSuffix)->handle()
        );
    }

    #[Test]
    public function handle_can_be_defined_as_a_property()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected static $handle = 'example';
        };

        $this->assertEquals('example', $fieldtype->handle());
    }

    #[Test]
    public function title_is_the_humanized_handle_by_default()
    {
        $this->assertEquals(
            'Test Multi Word',
            (new TestMultiWordFieldtype)->title()
        );

        $this->assertEquals(
            'Test Multi Word With No Fieldtype Suffix',
            (new TestMultiWordWithNoFieldtypeSuffix)->title()
        );
    }

    #[Test]
    public function title_can_be_defined_as_a_property()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected static $title = 'Super Cool Example';
        };

        $this->assertEquals('Super Cool Example', $fieldtype->title());
    }

    #[Test]
    public function localization_can_be_disabled()
    {
        $this->assertTrue((new TestFieldtype)->localizable());

        $fieldtype = new class extends Fieldtype
        {
            protected $localizable = false;
        };

        $this->assertFalse($fieldtype->localizable());
    }

    #[Test]
    public function validation_can_be_disabled()
    {
        $this->assertTrue((new TestFieldtype)->validatable());

        $fieldtype = new class extends Fieldtype
        {
            protected $validatable = false;
        };

        $this->assertFalse($fieldtype->validatable());
    }

    #[Test]
    public function default_values_can_be_disabled()
    {
        $this->assertTrue((new TestFieldtype)->defaultable());

        $fieldtype = new class extends Fieldtype
        {
            protected $defaultable = false;
        };

        $this->assertFalse($fieldtype->defaultable());
    }

    #[Test]
    public function it_can_be_flagged_as_hidden_from_the_fieldtype_selector()
    {
        $this->assertTrue((new TestFieldtype)->selectable());

        $fieldtype = new class extends Fieldtype
        {
            protected $selectable = false;
        };

        $this->assertFalse($fieldtype->selectable());
    }

    #[Test]
    public function it_can_be_flagged_as_a_relationship_fieldtype()
    {
        $this->assertFalse((new TestFieldtype)->isRelationship());

        $fieldtype = new class extends Fieldtype
        {
            protected $relationship = true;
        };

        $this->assertTrue($fieldtype->isRelationship());
    }

    #[Test]
    public function converts_to_an_array()
    {
        $fieldtype = new TestFieldtype;

        $this->assertEquals([
            'handle' => 'test',
            'title' => 'Test',
            'localizable' => true,
            'validatable' => true,
            'defaultable' => true,
            'categories' => [],
            'keywords' => [],
            'icon' => 'test',
            'config' => [],
        ], $fieldtype->toArray());
    }

    #[Test]
    public function config_uses_publish_array_when_converting_to_array()
    {
        $fields = Mockery::mock(Fields::class);
        $fields->shouldReceive('toPublishArray')->once()->andReturn(['example', 'publish', 'array']);

        $fieldtype = new class($fields) extends Fieldtype
        {
            protected $mock;
            protected static $handle = 'test';

            public function __construct($mock)
            {
                $this->mock = $mock;
            }

            public function configFields(): Fields
            {
                return $this->mock;
            }
        };

        $this->assertArraySubset([
            'config' => ['example', 'publish', 'array'],
        ], $fieldtype->toArray());
    }

    #[Test]
    public function it_gets_custom_validation_rules_as_an_array()
    {
        $this->assertEquals([], (new TestFieldtype)->rules());

        $arrayDefined = new class extends Fieldtype
        {
            protected $rules = ['required', 'min:2'];
        };
        $this->assertEquals(['required', 'min:2'], $arrayDefined->rules());

        $stringDefined = new class extends Fieldtype
        {
            protected $rules = 'required|min:2';
        };
        $this->assertEquals(['required', 'min:2'], $stringDefined->rules());
    }

    #[Test]
    public function it_gets_extra_custom_validation_rules_as_an_array()
    {
        $this->assertEquals([], (new TestFieldtype)->rules());

        $arrayDefined = new class extends Fieldtype
        {
            protected $extraRules = [
                'extra.one' => ['required', 'min:2'],
                'extra.two' => ['array'],
            ];
        };
        $this->assertEquals([
            'extra.one' => ['required', 'min:2'],
            'extra.two' => ['array'],
        ], $arrayDefined->extraRules());

        $stringDefined = new class extends Fieldtype
        {
            protected $extraRules = [
                'extra.one' => 'required|min:2',
                'extra.two' => 'array',
            ];
        };
        $this->assertEquals([
            'extra.one' => ['required', 'min:2'],
            'extra.two' => ['array'],
        ], $stringDefined->extraRules());
    }

    #[Test]
    public function it_can_have_a_default_value()
    {
        $this->assertNull((new TestFieldtype)->defaultValue());

        $fieldtype = new class extends Fieldtype
        {
            protected $defaultValue = 'test';
        };

        $this->assertEquals('test', $fieldtype->defaultValue());
    }

    #[Test]
    public function it_gets_the_config_fields()
    {
        tap(new TestFieldtype, function ($fieldtype) {
            $fields = $fieldtype->configFields();
            $this->assertInstanceOf(Fields::class, $fields);
            $this->assertCount(0, $fields->all());
        });

        $fieldtype = new class extends Fieldtype
        {
            protected $configFields = [
                'foo' => ['type' => 'textarea'],
                'max_items' => ['type' => 'integer'],
            ];
        };

        $fields = $fieldtype->configFields();
        $this->assertInstanceOf(ConfigFields::class, $fields);
        $this->assertCount(2, $all = $fields->all());
        tap($all['foo'], function ($field) {
            $this->assertEquals('textarea', $field->type());
        });
        tap($all['max_items'], function ($field) {
            $this->assertEquals('integer', $field->type());
        });
    }

    #[Test]
    public function it_can_append_a_single_config_field()
    {
        TestAppendConfigFields::appendConfigField('group', ['type' => 'text']);

        $fields = (new TestAppendConfigFields())->configFields();

        $this->assertCount(3, $fields->all());
        $this->assertEquals('text', $fields->get('group')->type());
    }

    #[Test]
    public function it_can_append_multiple_config_fields()
    {
        TestAppendConfigFields::appendConfigFields([
            'group' => [
                'type' => 'text',
            ],
            'description' => [
                'type' => 'textarea',
            ],
        ]);

        $fields = (new TestAppendConfigFields())->configFields();

        $this->assertCount(4, $fields->all());
        $this->assertEquals('text', $fields->get('group')->type());
        $this->assertEquals('textarea', $fields->get('description')->type());
    }

    #[Test]
    public function it_wont_override_previously_appended_config_fields()
    {
        TestAppendConfigFields::appendConfigFields([
            'group' => [
                'type' => 'text',
            ],
            'description' => [
                'type' => 'textarea',
            ],
        ]);

        TestAppendConfigFields::appendConfigField('another', ['type' => 'text']);

        $fields = (new TestAppendConfigFields())->configFields();

        $this->assertCount(5, $fields->all());
        $this->assertEquals('text', $fields->get('group')->type());
        $this->assertEquals('textarea', $fields->get('description')->type());
        $this->assertEquals('text', $fields->get('another')->type());
    }

    #[Test]
    public function it_will_only_append_config_fields_to_the_intended_fieldtype()
    {
        $fieldtype = new class extends Fieldtype
        {
        };

        $fieldtypeWithAppendedConfig = new class extends Fieldtype
        {
        };

        $fieldtypeWithAppendedConfig::appendConfigField('group', ['type' => 'text']);

        $this->assertCount(0, $fieldtype->configFields()->all());
        $this->assertCount(1, $fieldtypeWithAppendedConfig->configFields()->all());
    }

    #[Test]
    #[DataProvider('configBlueprintProvider')]
    public function it_gets_the_config_blueprint($property, $expectedSections, $expectedConfigFields)
    {
        $fieldtype = new TestFieldtypeWithConfigFieldsProperty($property);

        TestFieldtypeWithConfigFieldsProperty::appendConfigField('appended', ['type' => 'text']);

        $this->assertInstanceOf(Blueprint::class, $blueprint = $fieldtype->configBlueprint());
        $this->assertEquals(['tabs' => [
            'main' => [
                'sections' => $expectedSections,
            ],
        ]], $blueprint->contents());

        $this->assertEquals($expectedConfigFields, $fieldtype->configFields()->all()->map(fn ($field) => $field->type())->all());
    }

    public static function configBlueprintProvider()
    {
        return [
            'linear fields results in one section' => [
                $configFields = [
                    'alfa' => ['type' => 'bravo'],
                    'charlie' => ['type' => 'delta'],
                ],
                $expectedSections = [
                    [
                        'fields' => [
                            ['handle' => 'alfa', 'field' => ['type' => 'bravo']],
                            ['handle' => 'charlie', 'field' => ['type' => 'delta']],
                            ['handle' => 'appended', 'field' => ['type' => 'text']], // appended field to the end of line section
                        ],
                    ],
                ],
                $expectedConfigFields = [
                    'alfa' => 'bravo',
                    'charlie' => 'delta',
                    'appended' => 'text',
                ],
            ],

            'single section' => [
                $configFields = [
                    [
                        'fields' => [
                            'alfa' => ['type' => 'bravo'],
                            'charlie' => ['type' => 'delta'],
                        ],
                    ],
                ],
                $expectedSections = [
                    [
                        'fields' => [
                            ['handle' => 'alfa', 'field' => ['type' => 'bravo']],
                            ['handle' => 'charlie', 'field' => ['type' => 'delta']],
                            ['handle' => 'appended', 'field' => ['type' => 'text']], // appended field to the end of lone section
                        ],
                    ],
                ],
                $expectedConfigFields = [
                    'alfa' => 'bravo',
                    'charlie' => 'delta',
                    'appended' => 'text',
                ],
            ],

            'multiple sections' => [
                $configFields = [
                    [
                        'fields' => [
                            'alfa' => ['type' => 'bravo'],
                            'charlie' => ['type' => 'delta'],
                        ],
                    ],
                    [
                        'fields' => [
                            'echo' => ['type' => 'foxtrot'],
                            'golf' => ['type' => 'hotel'],
                        ],
                    ],
                ],
                $expectedSections = [
                    [
                        'fields' => [
                            ['handle' => 'alfa', 'field' => ['type' => 'bravo']],
                            ['handle' => 'charlie', 'field' => ['type' => 'delta']],
                        ],
                    ],
                    [
                        'fields' => [
                            ['handle' => 'echo', 'field' => ['type' => 'foxtrot']],
                            ['handle' => 'golf', 'field' => ['type' => 'hotel']],
                        ],
                    ],
                    [
                        'fields' => [
                            ['handle' => 'appended', 'field' => ['type' => 'text']], // appended field goes into its own section
                        ],
                    ],
                ],
                $expectedConfigFields = [
                    'alfa' => 'bravo',
                    'charlie' => 'delta',
                    'echo' => 'foxtrot',
                    'golf' => 'hotel',
                    'appended' => 'text',
                ],
            ],
        ];
    }

    #[Test]
    public function it_can_have_an_icon()
    {
        $this->assertEquals('test', (new TestFieldtype)->icon());

        $customHandle = new class extends Fieldtype
        {
            protected static $handle = 'custom_handle';
        };

        $this->assertEquals('custom_handle', $customHandle->icon());

        $customIcon = new class extends Fieldtype
        {
            protected $icon = 'foo';
        };

        $this->assertEquals('foo', $customIcon->icon());
    }

    #[Test]
    public function no_processing_happens_by_default()
    {
        $this->assertEquals('test', (new TestFieldtype)->process('test'));
    }

    #[Test]
    public function no_pre_processing_happens_by_default()
    {
        $this->assertEquals('test', (new TestFieldtype)->preProcess('test'));
    }

    #[Test]
    public function no_pre_processing_happens_by_default_for_the_index()
    {
        $this->assertEquals('test', (new TestFieldtype)->preProcessIndex('test'));
    }

    #[Test]
    public function it_gets_a_config_value()
    {
        (new class extends Fieldtype
        {
            protected static $handle = 'fieldtype_with_array_default';
            protected $defaultValue = [];
        })::register();

        $field = new Field('test', [
            'foo' => 'bar', // doesn't exist as a config field
            'alfa' => 'overridden', // doesn't have a default
            'bravo' => 'also overridden', // does have a default
        ]);

        $fieldtype = (new TestFieldtypeWithConfigFields)->setField($field);

        $this->assertEquals([
            'foo' => 'bar',
            'alfa' => 'overridden',
            'bravo' => 'also overridden',
            'charlie' => 'charlie!',
            // Toggle fields (has default of boolean false)
            'delta' => false, // No default set
            'echo' => true, // Default set
            'foxtrot' => false, // Default set
            // Test fields (has default of empty array)
            'golf' => [], // No default set
            'hotel' => ['hotel!'], // Default set
        ], $fieldtype->config());
        $this->assertEquals('bar', $fieldtype->config('foo'));
        $this->assertEquals('overridden', $fieldtype->config('alfa'));
        $this->assertEquals('also overridden', $fieldtype->config('bravo'));
        $this->assertEquals('charlie!', $fieldtype->config('charlie'));
        $this->assertEquals(false, $fieldtype->config('delta'));
        $this->assertEquals(true, $fieldtype->config('echo'));
        $this->assertEquals(false, $fieldtype->config('foxtrot'));
        $this->assertEquals([], $fieldtype->config('golf'));
        $this->assertEquals(['hotel!'], $fieldtype->config('hotel'));
        $this->assertNull($fieldtype->config('unknown'));
        $this->assertEquals('fallback', $fieldtype->config('unknown', 'fallback'));
    }

    #[Test]
    #[Group('graphql')]
    public function it_gets_the_graphql_type_of_string_by_default()
    {
        $type = (new TestFieldtype)->toGqlType();

        $this->assertInstanceOf(\GraphQL\Type\Definition\StringType::class, $type);
    }

    #[Test]
    public function it_can_make_a_fieldtype_selectable_in_forms()
    {
        $fieldtype = new class extends Fieldtype
        {
            public static $handle = 'test';
        };

        $this->assertFalse($fieldtype->selectableInForms());
        $this->assertFalse(FieldtypeRepository::hasBeenMadeSelectableInForms('test'));

        $fieldtype::makeSelectableInForms();

        $this->assertTrue($fieldtype->selectableInForms());
        $this->assertTrue(FieldtypeRepository::hasBeenMadeSelectableInForms('test'));
    }

    #[Test]
    public function it_can_make_a_fieldtype_unselectable_in_forms()
    {
        $fieldtype = new class extends Fieldtype
        {
            public static $handle = 'test';
        };

        $fieldtype::makeSelectableInForms();

        $this->assertTrue($fieldtype->selectableInForms());
        $this->assertTrue(FieldtypeRepository::hasBeenMadeSelectableInForms('test'));

        $fieldtype::makeUnselectableInForms();

        $this->assertFalse($fieldtype->selectableInForms());
        $this->assertFalse(FieldtypeRepository::hasBeenMadeSelectableInForms('test'));
    }
}

class TestFieldtype extends Fieldtype
{
    //
}

class TestFieldtypeWithConfigFields extends Fieldtype
{
    protected $configFields = [
        'alfa' => [
            'type' => 'text',
        ],
        'bravo' => [
            'type' => 'text',
            'default' => 'bravo!',
        ],
        'charlie' => [
            'type' => 'text',
            'default' => 'charlie!',
        ],
        'delta' => [
            'type' => 'toggle',
        ],
        'echo' => [
            'type' => 'toggle',
            'default' => true,
        ],
        'foxtrot' => [
            'type' => 'toggle',
            'default' => false,
        ],
        'golf' => [
            'type' => 'fieldtype_with_array_default',
        ],
        'hotel' => [
            'type' => 'fieldtype_with_array_default',
            'default' => ['hotel!'],
        ],
    ];
}

class TestMultiWordFieldtype extends Fieldtype
{
    //
}

class TestMultiWordWithNoFieldtypeSuffix extends Fieldtype
{
    //
}

class TestAppendConfigFields extends Fieldtype
{
    protected $configFields = [
        'foo' => ['type' => 'textarea'],
        'max_items' => ['type' => 'integer'],
    ];
}

class TestFieldtypeWithConfigFieldsProperty extends Fieldtype
{
    public function __construct($property)
    {
        $this->configFields = $property;
    }
}
