<?php

namespace Tests\Antlers\Runtime;

use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\ButtonGroup;
use Statamic\Fieldtypes\Checkboxes;
use Statamic\Fieldtypes\Floatval;
use Statamic\Fieldtypes\Integer;
use Tests\Antlers\ParserTestCase;

class ConditionalLogicValueTest extends ParserTestCase
{
    public function test_conditionals_handle_values_transparently()
    {
        $integerFieldType = new Integer();
        $floatFieldType = new Floatval();
        $checkboxFieldType = new Checkboxes();
        $buttonFieldType = new ButtonGroup();
        $buttonFieldType->setField(new Field('button_value', [
            'options' => [
                'one' => 'One',
                'two' => 'Two',
                'three' => 'Three',
            ],
            'type' => 'button_group',
        ]));
        $checkboxFieldType->setField(new Field('checkbox_value', [
            'inline' => false,
            'options' => [
                'option_one' => 'Option One',
                'option_two' => 'Option Two',
            ],
            'type' => 'checkboxes',
        ]));

        $integerValue = new Value(42, 'integer_value', $integerFieldType);
        $floatValue = new Value(42.00, 'float_value', $floatFieldType);
        $checkboxValue = new Value(['option_one'], 'checkbox_value', $checkboxFieldType);
        $buttonGroupValue = new Value('two', 'button_value', $buttonFieldType);
        $buttonGroupValueTwo = new Value('two', 'button_value_two', $buttonFieldType);
        $buttonGroupValueThree = new Value('three', 'button_value_three', $buttonFieldType);

        $data = [
            'integer_value' => $integerValue,
            'float_value' => $floatValue,
            'checkbox_value' => $checkboxValue,
            'button_value' => $buttonGroupValue,
            'button_value_two' => $buttonGroupValueTwo,
            'button_value_three' => $buttonGroupValueThree,
        ];

        $this->assertSame('Yes', $this->renderString('{{ if checkbox_value.0.value == "option_one" }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ (button_value === "two") ? "Yes" : "No" }}', $data));
        $this->assertSame('No', $this->renderString('{{ (button_value === "three") ? "Yes" : "No" }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ (button_value === "two") ?= "Yes" }}', $data));
        $this->assertSame('', $this->renderString('{{ (button_value === "three") ?= "Yes" }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if button_value === "two" }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if button_value === button_value_two }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if button_value == button_value_two }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('No', $this->renderString('{{ if button_value === button_value_three }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('No', $this->renderString('{{ if button_value == button_value_three }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if button_value === button_value }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if button_value == "two" }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if button_value == button_value }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if (checkbox_value | raw | contains("option_one")) }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if integer_value > 40 }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if integer_value > "40" }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if integer_value >= float_value }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if integer_value >= integer_value }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('No', $this->renderString('{{ if integer_value < float_value }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('No', $this->renderString('{{ if integer_value === float_value }}Yes{{ else }}No{{ /if }}', $data));
        $this->assertSame('Yes', $this->renderString('{{ if integer_value == float_value }}Yes{{ else }}No{{ /if }}', $data));
    }
}
