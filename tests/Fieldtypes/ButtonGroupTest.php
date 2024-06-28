<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\ButtonGroup;
use Tests\TestCase;

class ButtonGroupTest extends TestCase
{
    use CastsBooleansTests, LabeledValueTests;

    private function field($config)
    {
        $ft = new ButtonGroup;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
    }

    #[Test]
    public function throws_a_validation_error_when_key_is_missing_from_option()
    {
        $fieldtype = FieldtypeRepository::find('button_group');
        $blueprint = $fieldtype->configBlueprint();

        $fields = $blueprint
            ->fields()
            ->addValues([
                'options' => [
                    'one' => 'One',
                    'two' => 'Two',
                    '' => 'Three',
                ],
            ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(__('statamic::validation.options_require_keys'));

        $fields->validate();
    }

    #[Test]
    public function does_not_throw_a_validation_error_when_all_options_have_keys()
    {
        $fieldtype = FieldtypeRepository::find('button_group');
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
}
