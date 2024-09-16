<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Select;
use Tests\TestCase;

class SelectTest extends TestCase
{
    use CastsBooleansTests, CastsMultipleBooleansTests, HasSelectOptionsTests, LabeledValueTests, MultipleLabeledValueTests;

    private function field($config)
    {
        $ft = new Select;

        return $ft->setField(new Field('test', array_merge($config, ['type' => $ft->handle()])));
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
}
