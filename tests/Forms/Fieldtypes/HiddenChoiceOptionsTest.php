<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Checkboxes;
use Statamic\Forms\Fieldtypes\Dropdown;
use Statamic\Forms\Fieldtypes\MultiChoice;
use Statamic\Forms\Summary\FieldResponses;
use Tests\TestCase;

class HiddenChoiceOptionsTest extends TestCase
{
    #[Test]
    #[DataProvider('fieldtypeProvider')]
    public function it_keeps_sequential_integer_keys_as_values($class, $type)
    {
        $fieldtype = $this->fieldtype($class, $type, [
            ['key' => 0, 'value' => 'No'],
            ['key' => 1, 'value' => 'Yes'],
        ]);

        $this->assertSame([
            ['key' => 0, 'value' => 'No'],
            ['key' => 1, 'value' => 'Yes'],
        ], $fieldtype->toFieldArray()['options']);

        $this->assertSame([
            ['value' => 0, 'label' => 'No'],
            ['value' => 1, 'label' => 'Yes'],
        ], $fieldtype->toField()->fieldtype()->preload()['options']);
    }

    #[Test]
    #[DataProvider('fieldtypeProvider')]
    public function it_keeps_hidden_options_marked_as_hidden_for_label_lookup($class, $type)
    {
        $fieldtype = $this->fieldtype($class, $type, [
            ['key' => 0, 'value' => 'No'],
            ['key' => 1, 'value' => 'Maybe', 'hidden' => true],
            ['key' => 2, 'value' => 'Yes'],
        ]);

        $this->assertSame([
            ['key' => 0, 'value' => 'No'],
            ['key' => 1, 'value' => 'Maybe', 'hidden' => true],
            ['key' => 2, 'value' => 'Yes'],
        ], $fieldtype->toFieldArray()['options']);

        $this->assertSame([
            ['value' => 0, 'label' => 'No'],
            ['value' => 1, 'label' => 'Maybe'],
            ['value' => 2, 'label' => 'Yes'],
        ], $fieldtype->toField()->fieldtype()->preload()['options']);
    }

    #[Test]
    #[DataProvider('fieldtypeProvider')]
    public function hidden_options_are_marked_as_hidden_in_the_augmented_form($class, $type)
    {
        $form = Form::make('survey')->formFields(['fields' => [[
            'handle' => 'answer',
            'field' => ['type' => $type, 'options' => [
                ['key' => 0, 'value' => 'No'],
                ['key' => 1, 'value' => 'Maybe', 'hidden' => true],
            ]],
        ]]]);

        $augmented = $form->toAugmentedArray(['fields', 'sections']);

        $expected = [
            ['key' => 0, 'value' => 'No'],
            ['key' => 1, 'value' => 'Maybe', 'hidden' => true],
        ];

        $this->assertSame($expected, $augmented['fields']->value()['answer']->get('options'));
        $this->assertSame($expected, $augmented['sections']->value()[0]['fields']['answer']['options']);
    }

    #[Test]
    #[DataProvider('fieldtypeProvider')]
    public function it_passes_through_options_without_hidden_options_untouched($class, $type)
    {
        $this->assertSame(
            ['red' => 'Red', 'blue' => 'Blue'],
            $this->fieldtype($class, $type, ['red' => 'Red', 'blue' => 'Blue'])->toFieldArray()['options']
        );

        $this->assertSame(
            ['red', 'blue'],
            $this->fieldtype($class, $type, ['red', 'blue'])->toFieldArray()['options']
        );
    }

    #[Test]
    #[DataProvider('fieldtypeProvider')]
    public function it_excludes_hidden_options_from_chart_options_unless_they_have_responses($class, $type)
    {
        $fieldtype = $this->fieldtype($class, $type, [
            ['key' => 0, 'value' => 'No'],
            ['key' => 1, 'value' => 'Maybe', 'hidden' => true],
            ['key' => 2, 'value' => 'Yes'],
            ['key' => 3, 'value' => 'Never', 'hidden' => true],
        ]);

        $options = $fieldtype->chartOptions($this->responses([]));

        $this->assertEquals([0, 2], $options->map->key->all());
        $this->assertEquals(['No', 'Yes'], $options->map->label->all());

        $options = $fieldtype->chartOptions($this->responses(['1', '2']));

        $this->assertEquals([0, 1, 2], $options->map->key->all());
        $this->assertEquals(['No', 'Maybe', 'Yes'], $options->map->label->all());
    }

    public static function fieldtypeProvider()
    {
        return [
            'checkboxes' => [Checkboxes::class, 'checkboxes'],
            'dropdown' => [Dropdown::class, 'dropdown'],
            'multi choice' => [MultiChoice::class, 'multi_choice'],
        ];
    }

    private function fieldtype(string $class, string $type, array $options)
    {
        return (new $class)->setField(new FormField('answer', ['type' => $type, 'options' => $options]));
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('answer', ['type' => 'multi_choice']), $values);
    }
}
