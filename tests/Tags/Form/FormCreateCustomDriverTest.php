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
    public function it_shows_js_driver_in_form_data()
    {
        $this->assertStringContainsString(
            '<span>custom_driver</span>',
            $this->tag('{{ form:contact js="custom_driver" }}<span>{{ js_driver }}</span>{{ /form:contact }}')
        );
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
    public function custom_driver_can_add_to_form_attributes()
    {
        $output = $this->tag('{{ form:contact js="custom_driver" }}{{ /form:contact }}');

        $expected = '<form method="POST" action="http://localhost/!/forms/contact" z-data="{\'lol\':\'catz\',\'handle\':\'contact\'}" z-rad="absolutely">';

        $this->assertStringContainsString($expected, $output);
    }

    /** @test */
    public function custom_driver_can_add_to_renderable_field_data()
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
    public function custom_driver_can_add_to_renderable_field_attributes()
    {
        $output = $this->normalizeHtml($this->tag(<<<'EOT'
{{ form:contact js="custom_driver" }}
    {{ fields }}
        {{ field }}
    {{ /fields }}
{{ /form:contact }}
EOT
        ));

        $expected = '<input type="email" name="email" value="" z-unless="Statamic.$conditions.showField(\'email\', __zData)" z-gnarley="true" required>';
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
    public function addToFormData($form, $data)
    {
        return [
            'custom_form_js' => "alert('the authorities')",
        ];
    }

    public function addToFormAttributes($form)
    {
        return [
            'z-data' => $this->jsonEncodeForHtmlAttribute(['lol' => 'catz', 'handle' => $form->handle()]),
            'z-rad' => 'absolutely',
        ];
    }

    public function addToRenderableFieldData($field, $data)
    {
        return [
            'show_field' => "alert('the stormtroopers')",
            'custom_field_js' => "alert('the sith')",
        ];
    }

    public function addToRenderableFieldAttributes($field)
    {
        return [
            'z-unless' => "Statamic.\$conditions.showField('{$field->handle()}', __zData)",
            'z-gnarley' => true,
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
