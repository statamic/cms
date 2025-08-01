<?php

namespace Tests\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Forms\Form;
use Tests\TestCase;

class FieldTest extends TestCase
{
    #[Test]
    public function it_gets_the_display_value()
    {
        $this->assertEquals(
            'Test Display Value',
            (new Field('test', ['display' => 'Test Display Value']))->display()
        );

        $this->assertEquals(
            'Test',
            (new Field('test', []))->display()
        );

        $this->assertEquals(
            'Test Multi Word Handle And No Explicit Display',
            (new Field('test_multi_word_handle_and_no_explicit_display', []))->display()
        );
    }

    #[Test]
    public function it_gets_instructions()
    {
        $this->assertEquals(
            'The instructions',
            (new Field('test', ['instructions' => 'The instructions']))->instructions()
        );

        $this->assertNull((new Field('test', []))->instructions());
    }

    #[Test]
    public function it_determines_if_localizable()
    {
        $this->assertFalse((new Field('test', []))->isLocalizable());
        $this->assertFalse((new Field('test', ['localizable' => false]))->isLocalizable());
        $this->assertTrue((new Field('test', ['localizable' => true]))->isLocalizable());
    }

    #[Test]
    public function it_gets_the_fieldtype()
    {
        $fieldtype = new class extends Fieldtype
        {
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('the_fieldtype')
            ->andReturnUsing(fn () => clone $fieldtype);
        $field = new Field('test', $config = ['type' => 'the_fieldtype', 'foo' => 'bar']);

        // The fieldtype from the repository should not have the field attached.
        $this->assertNull($fieldtype->field());

        // The fieldtype from the field should be an instance of that
        // fieldtype class, and should have the field attached.
        $this->assertInstanceOf(get_class($fieldtype), $field->fieldtype());
        $this->assertEquals($field->config(), $field->fieldtype()->field()->config());

        // Double check that the fieldtype from the repository still doesn't somehow have the field attached.
        $this->assertNull(FieldtypeRepository::find('the_fieldtype')->field());
    }

    #[Test]
    public function it_gets_validation_rules_from_field()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected $rules = null;
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_no_rules')
            ->andReturn($fieldtype);

        $field = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'required|min:2',
        ]);

        $this->assertEquals([
            'test' => ['required', 'min:2'],
        ], $field->rules());
    }

    #[Test]
    public function it_gets_validation_rules_from_fieldtype()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected $rules = 'min:2|max:5';
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_rules')
            ->andReturn($fieldtype);

        $field = new Field('test', ['type' => 'fieldtype_with_rules']);

        $this->assertEquals([
            'test' => ['min:2', 'max:5', 'nullable'],
        ], $field->rules());
    }

    #[Test]
    public function it_merges_validation_rules_from_field_with_fieldtype()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected $rules = 'min:2|max:5';
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_rules')
            ->andReturn($fieldtype);

        $field = new Field('test', [
            'type' => 'fieldtype_with_rules',
            'validate' => 'required|array',
        ]);

        $this->assertEquals([
            'test' => ['required', 'array', 'min:2', 'max:5'],
        ], $field->rules());
    }

    #[Test]
    public function it_merges_extra_fieldtype_rules()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected $extraRules = [
                'test.*.one' => 'required|min:2',
                'test.*.two' => 'max:2',
            ];
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_extra_rules')
            ->andReturn($fieldtype);

        $field = new Field('test', [
            'type' => 'fieldtype_with_extra_rules',
            'validate' => 'required',
        ]);

        $this->assertEquals([
            'test' => ['required'],
            'test.*.one' => ['required', 'min:2'],
            'test.*.two' => ['max:2', 'nullable'],
        ], $field->rules());
    }

    #[Test]
    public function it_checks_if_a_field_is_required_when_defined_in_field()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected $rules = null;
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_no_rules')
            ->andReturn($fieldtype);

        $requiredField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'required|min:2',
        ]);

        $optionalField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'min:2',
        ]);

        $this->assertTrue($requiredField->isRequired());
        $this->assertFalse($optionalField->isRequired());
    }

    #[Test]
    public function it_checks_if_a_field_is_required_when_defined_in_fieldtype()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected $rules = 'required|min:2';
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_rules')
            ->andReturn($fieldtype);

        $field = new Field('test', [
            'type' => 'fieldtype_with_rules',
            'validate' => 'min:2',
        ]);

        $this->assertTrue($field->isRequired());
    }

    #[Test]
    public function it_checks_if_a_field_is_required_when_defined_as_its_own_field_property()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected $rules = null;
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_no_rules')
            ->andReturn($fieldtype);

        $requiredField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'min:2',
            'required' => true,
        ]);

        $optionalField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'min:2',
        ]);

        $explicitlyOptionalField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'min:2',
            'required' => false,
        ]);

        $this->assertTrue($requiredField->isRequired());
        $this->assertFalse($optionalField->isRequired());
        $this->assertFalse($explicitlyOptionalField->isRequired());
    }

    #[Test]
    public function it_adds_nullable_rule_when_not_required()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected $rules = null;
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype_with_no_rules')
            ->andReturn($fieldtype);

        $nullableField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'min:2',
        ]);

        $booleanRequiredField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'min:2',
            'required' => true,
        ]);

        $validateRequiredField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'required|min:2',
        ]);

        $validateRequiredIfField = new Field('test', [
            'type' => 'fieldtype_with_no_rules',
            'validate' => 'required_if:foo|min:2',
        ]);

        $this->assertEquals(['test' => ['min:2', 'nullable']], $nullableField->rules());
        $this->assertEquals(['test' => ['required', 'min:2']], $booleanRequiredField->rules());
        $this->assertEquals(['test' => ['required', 'min:2']], $validateRequiredField->rules());
        $this->assertEquals(['test' => ['required_if:foo', 'min:2']], $validateRequiredIfField->rules());
    }

    #[Test]
    public function converts_to_array_suitable_for_rendering_fields_in_publish_component()
    {
        FieldtypeRepository::partialMock();

        FieldtypeRepository::shouldReceive('find')
            ->with('example')
            ->andReturn(new class extends Fieldtype
            {
                protected $component = 'example';
                protected $configFields = [
                    'a_config_field_with_pre_processing' => ['type' => 'with_processing'],
                    'a_config_field_without_pre_processing' => ['type' => 'without_processing'],
                ];
            });

        FieldtypeRepository::shouldReceive('find')
            ->with('with_processing')
            ->andReturn(new class extends Fieldtype
            {
                public function preProcess($data)
                {
                    return $data.' preprocessed';
                }
            });

        FieldtypeRepository::shouldReceive('find')
            ->with('without_processing')
            ->andReturn(new class extends Fieldtype
            {
                public function preProcess($data)
                {
                    return $data;
                }
            });

        $field = new Field('test', [
            'type' => 'example',
            'display' => 'Test Field',
            'instructions' => 'Test instructions',
            'instructions_position' => 'below',
            'validate' => 'required',
            'foo' => 'bar',
            'a_config_field_with_pre_processing' => 'foo',
            'a_config_field_without_pre_processing' => 'foo',
        ]);

        $this->assertSame([
            'display' => 'Test Field',
            'hide_display' => false,
            'handle' => 'test',
            'instructions' => 'Test instructions',
            'instructions_position' => 'below',
            'listable' => 'hidden',
            'sortable' => true,
            'visibility' => 'visible',
            'replicator_preview' => true,
            'duplicate' => true,
            'type' => 'example',
            'validate' => 'required',
            'foo' => 'bar',
            'a_config_field_with_pre_processing' => 'foo preprocessed',
            'a_config_field_without_pre_processing' => 'foo',
            'component' => 'example',
            'prefix' => null,
            'required' => true,
            'read_only' => false, // deprecated
            'always_save' => false,
        ], $field->toPublishArray());
    }

    #[Test]
    public function it_gets_the_value()
    {
        $field = (new Field('test', ['type' => 'fieldtype']));
        $this->assertNull($field->value());

        $return = $field->setValue('foo');

        $this->assertEquals($field, $return);
        $this->assertEquals('foo', $field->value());
    }

    #[Test]
    public function it_processes_the_value_through_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn(new class extends Fieldtype
            {
                public function process($data)
                {
                    return $data.' processed';
                }
            });

        $field = (new Field('test', ['type' => 'fieldtype']))->setValue('foo');

        $processed = $field->process();

        $this->assertNotSame($field, $processed);
        $this->assertEquals('foo processed', $processed->value());
    }

    #[Test]
    public function it_preprocesses_the_value_through_its_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn(new class extends Fieldtype
            {
                public function preProcess($data)
                {
                    return $data.' preprocessed';
                }
            });

        $field = (new Field('test', ['type' => 'fieldtype']))->setValue('foo');

        $preProcessed = $field->preProcess();

        $this->assertNotSame($field, $preProcessed);
        $this->assertEquals('foo preprocessed', $preProcessed->value());
    }

    #[Test]
    public function it_preprocesses_the_value_through_its_fieldtype_for_the_index()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn(new class extends Fieldtype
            {
                public function preProcessIndex($data)
                {
                    return $data.' preprocessed for index';
                }
            });

        $field = (new Field('test', ['type' => 'fieldtype']))->setValue('foo');

        $preProcessed = $field->preProcessIndex();

        $this->assertNotSame($field, $preProcessed);
        $this->assertEquals('foo preprocessed for index', $preProcessed->value());
    }

    #[Test]
    public function preprocessing_a_field_with_no_value_will_take_the_default_from_the_field()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn(new class extends Fieldtype
            {
                public function preProcess($data)
                {
                    return $data.' preprocessed';
                }
            });

        $field = (new Field('test', [
            'type' => 'fieldtype',
            'default' => 'field defined default',
        ]));

        $this->assertEquals('field defined default preprocessed', $field->preProcess()->value());
    }

    #[Test]
    public function preprocessing_a_field_with_no_value_and_no_field_defined_default_value_will_take_the_default_from_the_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn(new class extends Fieldtype
            {
                public function preProcess($data)
                {
                    return $data.' preprocessed';
                }

                public function defaultValue()
                {
                    return 'fieldtype defined default';
                }
            });

        $field = (new Field('test', ['type' => 'fieldtype']));

        $this->assertEquals('fieldtype defined default preprocessed', $field->preProcess()->value());
    }

    #[Test]
    public function converting_to_an_array_will_inline_the_handle()
    {
        $field = new Field('the_handle', ['foo' => 'bar']);

        $this->assertEquals([
            'handle' => 'the_handle',
            'foo' => 'bar',
            'width' => 100,
        ], $field->toArray());
    }

    #[Test]
    public function it_gets_and_sets_the_config()
    {
        $field = new Field('the_handle', ['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $field->config());

        $return = $field->setConfig(['bar' => 'baz']);

        $this->assertEquals($field, $return);
        $this->assertEquals(['bar' => 'baz'], $field->config());
    }

    #[Test]
    public function it_makes_a_new_instance()
    {
        $field = new Field('test', ['foo' => 'bar']);
        $field->setValue('the value');

        $newField = $field->newInstance();

        $this->assertNotSame($field, $newField);
        $this->assertSame($field->handle(), $newField->handle());
        $this->assertSame($field->config(), $newField->config());
        $this->assertSame($field->value(), $newField->value());
    }

    #[Test]
    public function it_augments_the_value_through_its_fieldtype()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function augment($data)
            {
                return $data.' augmented';
            }

            public function shallowAugment($data)
            {
                return $data.' shallow augmented';
            }
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn($fieldtype);

        $field = (new Field('test', ['type' => 'fieldtype']))->setValue('foo');

        tap($field->augment(), function ($augmented) use ($field, $fieldtype) {
            $this->assertNotSame($field, $augmented);
            $value = $augmented->value();
            $this->assertInstanceOf(Value::class, $value);
            $this->assertEquals($fieldtype, $value->fieldtype());
            $this->assertEquals('test', $value->handle());
            $this->assertEquals('foo', $value->raw());
            $this->assertEquals('foo augmented', $value->value());
        });

        tap($field->shallowAugment(), function ($augmented) use ($field, $fieldtype) {
            $this->assertNotSame($field, $augmented);
            $value = $augmented->value();
            $this->assertInstanceOf(Value::class, $value);
            $this->assertEquals($fieldtype, $value->fieldtype());
            $this->assertEquals('test', $value->handle());
            $this->assertEquals('foo', $value->raw());
            $this->assertEquals('foo shallow augmented', $value->value());
        });
    }

    #[Test]
    #[Group('graphql')]
    public function it_gets_the_graphql_type()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function toGqlType()
            {
                return new \GraphQL\Type\Definition\FloatType;
            }
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn($fieldtype);

        $field = new Field('test', ['type' => 'fieldtype']);

        $type = $field->toGql();

        $this->assertIsArray($type);
        $this->assertInstanceOf(\GraphQL\Type\Definition\NullableType::class, $type['type']);
        $this->assertInstanceOf(\GraphQL\Type\Definition\FloatType::class, $type['type']);
    }

    #[Test]
    #[Group('graphql')]
    public function it_makes_the_graphql_type_non_nullable_if_its_required()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function toGqlType()
            {
                return new \GraphQL\Type\Definition\FloatType;
            }
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn($fieldtype);

        $field = new Field('test', ['type' => 'fieldtype', 'validate' => 'required']);

        $type = $field->toGql();

        $this->assertIsArray($type);
        $this->assertInstanceOf(\GraphQL\Type\Definition\NonNull::class, $type['type']);
        $this->assertInstanceOf(\GraphQL\Type\Definition\FloatType::class, $type['type']->getWrappedType());
    }

    #[Test]
    #[Group('graphql')]
    public function it_keeps_the_graphql_type_nullable_if_its_sometimes_required()
    {
        $fieldtype = new class extends Fieldtype
        {
            public function toGqlType()
            {
                return new \GraphQL\Type\Definition\FloatType;
            }
        };

        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn($fieldtype);

        $field = new Field('test', ['type' => 'fieldtype', 'validate' => 'required|sometimes']);

        $type = $field->toGql();

        $this->assertIsArray($type);
        $this->assertInstanceOf(\GraphQL\Type\Definition\NullableType::class, $type['type']);
        $this->assertInstanceOf(\GraphQL\Type\Definition\FloatType::class, $type['type']);
    }

    #[Test]
    public function it_gets_the_path_of_handles_for_nested_fields()
    {
        $top = (new Field('a', ['type' => 'text']));
        $second = (new Field('b', ['type' => 'text']))->setParentField($top);
        $third = (new Field('c', ['type' => 'text']))->setParentField($second);

        $this->assertEquals(['a'], $top->handlePath());
        $this->assertEquals(['a', 'b'], $second->handlePath());
        $this->assertEquals(['a', 'b', 'c'], $third->handlePath());
    }

    #[Test]
    public function it_checks_if_its_a_relationship_through_the_fieldtype()
    {
        FieldtypeRepository::shouldReceive('find')
            ->with('fieldtype')
            ->andReturn(new class extends Fieldtype
            {
                public function isRelationship(): bool
                {
                    return true;
                }
            });

        $field = (new Field('test', ['type' => 'fieldtype']))->setValue('foo');

        $this->assertTrue($field->isRelationship());
    }

    #[Test]
    public function it_gets_and_sets_the_form()
    {
        $field = new Field('test', ['type' => 'text']);

        $this->assertNull($field->form());

        $return = $field->setForm($form = new Form);

        $this->assertEquals($field, $return);
        $this->assertEquals($form, $field->form());
    }
}
