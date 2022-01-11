<?php

namespace Tests\Tags\Form;

use Statamic\Forms\JsDrivers\AbstractJsDriver;

class FormCreateCustomDriverTest extends FormTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        CustomDriver::register();
    }

    /** @test */
    public function custom_driver_can_add_to_form_attributes()
    {
        $output = $this->tag('{{ form:contact js="custom_driver" }}{{ /form:contact }}');

        $expected = '<form method="POST" action="http://localhost/!/forms/contact" z-data="{\'lol\':\'catz\'}" z-rad="absolutely">';

        $this->assertStringContainsString($expected, $output);
    }

    /** @test */
    public function custom_driver_can_add_to_form_data()
    {
        $output = $this->tag(<<<'EOT'
{{ form:contact js="custom_driver" }}
    <script>{{ custom_form_js }}</script>
{{ /form:contact }}
EOT
        );

        $expected = "<script>alert('the authorities')</script>";

        $this->assertStringContainsString($expected, $output);
    }

    /** @test */
    public function custom_driver_can_add_to_field_data()
    {
        $output = $this->tag(<<<'EOT'
{{ form:contact js="custom_driver" }}
    {{ fields }}
        <script>{{ custom_field_js }}</script>
    {{ /fields }}
{{ /form:contact }}
EOT
        );

        $expected = "<script>alert('the sith')</script>";

        $this->assertStringContainsString($expected, $output);
    }

    /** @test */
    public function it_validates_required_show_field_output_in_renderable_field_data()
    {
        CustomDriverWithoutShowField::register();

        $this->expectExceptionMessage('JS driver requires [show_field] to be defined in [addToRenderableFieldData()] output!');

        $this->tag('{{ form:contact js="custom_driver_without_show_field" }}<span>{{ js_driver }}</span>{{ /form:contact }}');
    }

    /** @test */
    public function custom_driver_get_show_field_js_in_dynamic_fields_array()
    {
        $output = $this->tag(<<<'EOT'
{{ form:contact js="custom_driver" }}
    {{ fields }}
        <script>{{ show_field }}</script>
    {{ /fields }}
{{ /form:contact }}
EOT
        );

        $expected = "<script>alert('the stormtroopers')</script>";

        $this->assertStringContainsString($expected, $output);
    }

    /** @test */
    public function custom_driver_get_show_field_js_at_top_level_for_when_hardcoding_field_html()
    {
        $output = $this->tag(<<<'EOT'
{{ form:contact js="custom_driver" }}
    <script>{{ show_field:name }}</script>
{{ /form:contact }}
EOT
        );

        $expected = "<script>alert('the stormtroopers')</script>";

        $this->assertStringContainsString($expected, $output);
    }
}

class CustomDriver extends AbstractJsDriver
{
    public function addToFormAttributes($data, $form)
    {
        return [
            'z-data' => $this->jsonEncodeForHtmlAttribute(['lol' => 'catz']),
            'z-rad' => 'absolutely',
        ];
    }

    public function addToFormData($data, $form)
    {
        return [
            'custom_form_js' => "alert('the authorities')",
        ];
    }

    public function addToRenderableFieldData($data, $field)
    {
        return [
            'show_field' => "alert('the stormtroopers')",
            'custom_field_js' => "alert('the sith')",
        ];
    }
}

class CustomDriverWithoutShowField extends AbstractJsDriver
{
    public function addToRenderableFieldData($data, $field)
    {
        return [
            // 'show_field' => 'This is required and should be validated at runtime',
        ];
    }
}
