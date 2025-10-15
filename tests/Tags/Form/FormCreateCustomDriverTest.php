<?php

namespace Tests\Tags\Form;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Forms\JsDrivers\AbstractJsDriver;
use Statamic\Statamic;

class FormCreateCustomDriverTest extends FormTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        CustomDriver::register();
    }

    #[Test]
    public function it_shows_js_driver_in_form_data()
    {
        $this->assertStringContainsString(
            '<span>custom_driver</span>',
            $this->tag('{{ form:contact js="custom_driver" }}<span>{{ js_driver }}</span>{{ /form:contact }}')
        );
    }

    #[Test]
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

    #[Test]
    public function custom_driver_can_add_to_form_attributes()
    {
        $output = $this->tag('{{ form:contact js="custom_driver" }}{{ /form:contact }}');

        $expectedZData = Statamic::modify(['lol' => 'catz', 'handle' => 'contact'])->toJson()->entities();

        $expected = '<form method="POST" action="http://localhost/!/forms/contact" z-data="'.$expectedZData.'" z-rad="absolutely">';

        $this->assertStringContainsString($expected, $output);
    }

    #[Test]
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

    #[Test]
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

        $expected = '<input id="contact-form-email-field" type="email" name="email" value="" z-unless="Statamic.$conditions.showField(\'email\', __zData)" z-gnarley="true" required>';
        $this->assertStringContainsString($expected, $output);
    }

    #[Test]
    public function it_validates_required_show_field_output_in_renderable_field_data()
    {
        CustomDriverWithoutShowField::register();

        $this->expectExceptionMessage('JS driver requires [show_field] to be defined in [addToRenderableFieldData()] output!');

        $this->tag('{{ form:contact js="custom_driver_without_show_field" }}<span>{{ js_driver }}</span>{{ /form:contact }}');
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function custom_driver_can_render_component_around_form()
    {
        $output = $this->tag('{{ form:contact js="custom_driver" }}{{ /form:contact }}');

        $this->assertStringContainsString('<custom-form-wrapper-component><form method="POST" action="http://localhost/!/forms/contact"', $output);
        $this->assertStringContainsString('</form></custom-form-wrapper-component>', $output);
    }

    #[Test]
    public function it_validates_render_method_returns_html_var()
    {
        $this->expectExceptionMessage('JS driver requires [$html] to be returned in [render()] output!');

        CustomDriverWithBadRenderMethod::register();

        $this->tag('{{ form:contact js="custom_driver_with_bad_render_method" }}{{ /form:contact }}');
    }

    #[Test]
    public function custom_driver_can_get_initial_form_data()
    {
        $driver = new CustomDriver(Form::find('contact'));

        $initialData = $driver->runProtectedGetInitialFormDataHelper();

        $expected = [
            'name' => null,
            'email' => null,
            'message' => null,
            'winnie' => null,
        ];

        $this->assertEquals($expected, $initialData);
    }

    #[Test]
    public function custom_driver_getting_initial_data_respects_old_data()
    {
        $this
            ->post('/!/forms/contact', [
                'name' => 'San Holo',
            ])
            ->assertSessionHasErrors(['email'], null, 'form.contact')
            ->assertLocation('/');

        $driver = new CustomDriver(Form::find('contact'));

        $initialData = $driver->runProtectedGetInitialFormDataHelper();

        $expected = [
            'name' => 'San Holo',
            'email' => null,
            'message' => null,
            'winnie' => null,
        ];

        $this->assertEquals($expected, $initialData);
    }
}

class CustomDriver extends AbstractJsDriver
{
    public function addToFormData($data)
    {
        return [
            'custom_form_js' => "alert('the authorities')",
        ];
    }

    public function addToFormAttributes()
    {
        return [
            'z-data' => Statamic::modify(['lol' => 'catz', 'handle' => $this->form->handle()])->toJson()->entities(),
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

    public function render($html)
    {
        return "<custom-form-wrapper-component>{$html}</custom-form-wrapper-component>";
    }

    public function runProtectedGetInitialFormDataHelper()
    {
        return $this->getInitialFormData();
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

class CustomDriverWithBadRenderMethod extends AbstractJsDriver
{
    public function render($html)
    {
        return 'oops, forgot to return $html var!';
    }
}
