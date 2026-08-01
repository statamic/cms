<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Select;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SelectTest extends TestCase
{
    use CastsBooleansTests, CastsMultipleBooleansTests, HasSelectOptionsTests, LabeledValueTests, MultipleLabeledValueTests;
    use PreventSavingStacheItemsToDisk;

    private function field($config, $parent = null, $parentField = null)
    {
        $ft = new Select;

        $field = new Field('test', array_merge($config, ['type' => $ft->handle()]));

        if ($parent) {
            $field->setParent($parent);
        }

        if ($parentField) {
            $field->setParentField($parentField);
        }

        return $ft->setField($field);
    }

    #[Test]
    public function throws_a_validation_error_when_key_is_missing_from_option()
    {
        $fieldtype = FieldtypeRepository::find('select');
        $blueprint = $fieldtype->configBlueprint();

        $fields = $blueprint
            ->fields()
            ->addValues([
                'options' => [
                    'one' => 'One',
                    'two' => 'Two',
                    'null' => 'Three',
                    '' => 'Four',
                ],
            ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__('statamic::validation.options_require_keys'));

        $fields->validate();
    }

    #[Test]
    public function does_not_throw_a_validation_error_when_all_options_have_keys()
    {
        $fieldtype = FieldtypeRepository::find('select');
        $blueprint = $fieldtype->configBlueprint();

        $fields = $blueprint
            ->fields()
            ->addValues($values = [
                'options' => [
                    'one' => 'One',
                    'two' => 'Two',
                    'three' => 'Three',
                ],
            ]);

        $this->assertEquals($values, $fields->validate());
    }

    #[Test]
    public function does_not_throw_a_validation_error_when_label_is_missing_from_option()
    {
        $fieldtype = FieldtypeRepository::find('select');
        $blueprint = $fieldtype->configBlueprint();

        $fields = $blueprint
            ->fields()
            ->addValues([
                'options' => [
                    'one' => null,
                    'two' => null,
                ],
            ]);

        $fields->validate();

        // If we've made it this far, it means we've passed validation
        // (otherwise an exception would be thrown).
        $this->assertTrue(true);
    }

    #[Test]
    public function it_does_not_discover_options_from_entries_when_taggable_is_disabled()
    {
        $collection = tap(Collection::make('products'))->save();

        EntryFactory::collection($collection)->slug('one')->data(['test' => 'custom'])->create();

        $field = $this->field([
            'options' => ['one' => 'One'],
            'taggable' => false,
        ], $collection);

        $this->assertSame([
            ['value' => 'one', 'label' => 'One'],
        ], $field->preload()['options']);
    }

    #[Test]
    public function it_discovers_options_from_entries_when_taggable_is_enabled()
    {
        $collection = tap(Collection::make('products'))->save();

        EntryFactory::collection($collection)->slug('one')->data(['test' => 'custom'])->create();
        EntryFactory::collection($collection)->slug('two')->data(['test' => 'another'])->create();
        EntryFactory::collection($collection)->slug('three')->data(['test' => 'custom'])->create();
        EntryFactory::collection($collection)->slug('four')->data(['test' => 'one'])->create();

        $field = $this->field([
            'options' => ['one' => 'One'],
            'taggable' => true,
        ], $collection);

        $this->assertSame([
            ['value' => 'one', 'label' => 'One'],
            ['value' => 'custom', 'label' => 'custom'],
            ['value' => 'another', 'label' => 'another'],
        ], $field->preload()['options']);
    }

    #[Test]
    public function it_flattens_multiple_select_values_when_discovering_options()
    {
        $collection = tap(Collection::make('products'))->save();

        EntryFactory::collection($collection)->slug('one')->data(['test' => ['alpha', 'beta']])->create();
        EntryFactory::collection($collection)->slug('two')->data(['test' => ['beta', 'gamma']])->create();

        $field = $this->field([
            'options' => ['alpha' => 'Alpha'],
            'taggable' => true,
            'multiple' => true,
        ], $collection);

        $this->assertSame([
            ['value' => 'alpha', 'label' => 'Alpha'],
            ['value' => 'beta', 'label' => 'beta'],
            ['value' => 'gamma', 'label' => 'gamma'],
        ], $field->preload()['options']);
    }

    #[Test]
    public function it_discovers_options_when_parent_is_an_entry()
    {
        $collection = tap(Collection::make('products'))->save();

        EntryFactory::collection($collection)->slug('one')->data(['test' => 'custom'])->create();
        $parent = EntryFactory::collection($collection)->slug('editing')->data(['test' => 'one'])->create();

        $field = $this->field([
            'options' => ['one' => 'One'],
            'taggable' => true,
        ], $parent);

        $this->assertSame([
            ['value' => 'one', 'label' => 'One'],
            ['value' => 'custom', 'label' => 'custom'],
        ], $field->preload()['options']);
    }

    #[Test]
    public function it_does_not_discover_options_without_a_collection_parent()
    {
        $field = $this->field([
            'options' => ['one' => 'One'],
            'taggable' => true,
        ]);

        $this->assertSame([
            ['value' => 'one', 'label' => 'One'],
        ], $field->preload()['options']);
    }

    #[Test]
    public function it_does_not_discover_options_for_nested_fields()
    {
        $collection = tap(Collection::make('products'))->save();

        EntryFactory::collection($collection)->slug('one')->data(['test' => 'custom'])->create();

        $parentField = new Field('replicator', ['type' => 'replicator']);

        $field = $this->field([
            'options' => ['one' => 'One'],
            'taggable' => true,
        ], $collection, $parentField);

        $this->assertSame([
            ['value' => 'one', 'label' => 'One'],
        ], $field->preload()['options']);
    }
}
