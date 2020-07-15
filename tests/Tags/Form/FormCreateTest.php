<?php

namespace Tests\Tags\Form;

use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\Parse;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormCreateTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->createContactForm();
        $this->clearSubmissions();
    }

    public function tearDown(): void
    {
        $this->clearSubmissions();

        parent::tearDown();
    }

    private function tag($tag)
    {
        return Parse::template($tag, []);
    }

    /** @test */
    public function it_renders_form()
    {
        $forms = [
            $this->tag('{{ form:create handle="contact" }}{{ /form:create }}'),
            $this->tag('{{ form:create is="contact" }}{{ /form:create }}'),
            $this->tag('{{ form:create in="contact" }}{{ /form:create }}'),
            $this->tag('{{ form:create form="contact" }}{{ /form:create }}'),
            $this->tag('{{ form:create formset="contact" }}{{ /form:create }}'),
            $this->tag('{{ form:contact }}{{ /form:contact }}'), // Shorthand
        ];

        $this->assertCount(6, $forms);

        foreach ($forms as $output) {
            $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/forms/contact">', $output);
            $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $output);
            $this->assertStringEndsWith('</form>', $output);
        }
    }

    /** @test */
    public function it_renders_form_with_params()
    {
        $output = $this->tag('{{ form:contact redirect="/submitted" error_redirect="/errors" class="form" id="form" }}{{ /form:contact }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/forms/contact" class="form" id="form">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/submitted" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="/errors" />', $output);
    }

    /** @test */
    public function it_renders_form_with_redirects_to_anchor()
    {
        $output = $this->tag('{{ form:contact redirect="#form" error_redirect="#form" }}{{ /form:contact }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="http://localhost#form" />', $output);
        $this->assertStringContainsString('<input type="hidden" name="_error_redirect" value="http://localhost#form" />', $output);
    }

    /** @test */
    public function it_renders_form_with_fields_array()
    {
        $output = $this->tag(<<<'EOT'
{{ form:contact }}
    {{ fields }}
        <label>{{ display }}</label>{{ field }}
    {{ /fields }}
{{ /form:contact }}
EOT
);

        $this->assertStringContainsString('<label>Full Name</label><input type="text" name="name" value="">', $output);
        $this->assertStringContainsString('<label>Email Address</label><input type="email" name="email" value="">', $output);
        $this->assertStringContainsString('<label>Message</label><textarea name="message" rows="5"></textarea>', $output);

        preg_match_all('/<label>(.+)<\/label>/U', $output, $fieldOrder);

        $this->assertEquals(['Full Name', 'Email Address', 'Message'], $fieldOrder[1]);
    }

    /** @test */
    public function it_wont_submit_form_and_renders_errors()
    {
        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->post('/!/forms/contact')
            ->assertSessionHasErrors(['email', 'message'], null, 'form.contact')
            ->assertLocation('/');

        $this->assertEmpty(Form::find('contact')->submissions());

        $output = $this->tag(<<<'EOT'
{{ form:contact }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="email-error">{{ error:email }}</p>
    <p class="success">{{ success }}</p>
{{ /form:contact }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="email-error">(.+)<\/p>/U', $output, $emailError);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $expected = [
            'The Email Address field is required.',
            'The Message field is required.',
        ];

        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expected[0], $emailError[1][0]);
        $this->assertEmpty($success[1]);
    }

    /** @test */
    public function it_will_submit_form_and_render_success()
    {
        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->post('/!/forms/contact', [
                'email' => 'san@holo.com',
                'message' => 'hello',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $this->assertCount(1, Form::find('contact')->submissions());

        $output = $this->tag(<<<'EOT'
{{ form:contact }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="success">{{ success }}</p>
{{ /form:contact }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="success">(.+)<\/p>/U', $output, $success);

        $this->assertEmpty($errors[1]);
        $this->assertEquals(['Submission successful.'], $success[1]);
    }

    /** @test */
    public function it_will_use_redirect_query_param_off_url()
    {
        $this->get('/?redirect=submission-successful&error_redirect=submission-failure');

        $expectedRedirect = '<input type="hidden" name="_redirect" value="submission-successful" />';
        $expectedErrorRedirect = '<input type="hidden" name="_error_redirect" value="submission-failure" />';

        $output = $this->tag('{{ form:contact }}{{ /form:contact }}');

        $this->assertStringNotContainsString($expectedRedirect, $output);
        $this->assertStringNotContainsString($expectedErrorRedirect, $output);

        $output = $this->tag('{{ form:contact allow_request_redirect="true" }}{{ /form:contact }}');

        $this->assertStringContainsString($expectedRedirect, $output);
        $this->assertStringContainsString($expectedErrorRedirect, $output);
    }

    /** @test */
    public function it_can_render_an_inline_error_when_multiple_rules_fail()
    {
        $this->withoutExceptionHandling();
        $this->assertEmpty(Form::find('contact')->submissions());

        $this
            ->post('/!/forms/contact', ['name' => '$'])
            ->assertSessionHasErrors(['name', 'email', 'message'], null, 'form.contact')
            ->assertLocation('/');

        $this->assertEmpty(Form::find('contact')->submissions());

        $output = $this->tag(<<<'EOT'
{{ form:contact }}
    {{ errors }}
        <p class="error">{{ value }}</p>
    {{ /errors }}
    <p class="inline-error">{{ error:name }}</p>
{{ /form:contact }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);
        preg_match_all('/<p class="inline-error">(.+)<\/p>/U', $output, $inlineErrors);

        $expected = [
            'The Full Name must be at least 3 characters.',
            'The Full Name may only contain letters and numbers.',
            'The Email Address field is required.',
            'The Message field is required.',
        ];

        $expectedInline = [
            'The Full Name must be at least 3 characters.',
        ];

        $this->assertEquals($expected, $errors[1]);
        $this->assertEquals($expectedInline, $inlineErrors[1]);
    }

    private function createContactForm()
    {
        $blueprint = Blueprint::make()->setContents([
            'fields' => [
                [
                    'handle' => 'name',
                    'field' => [
                        'type' => 'text',
                        'display' => 'Full Name',
                        'validate' => 'min:3|alpha_num',
                    ],
                ],
                [
                    'handle' => 'email',
                    'field' => [
                        'type' => 'text',
                        'input_type' => 'email',
                        'display' => 'Email Address',
                        'validate' => 'required|email',
                    ],
                ],
                [
                    'handle' => 'message',
                    'field' => [
                        'type' => 'textarea',
                        'display' => 'Message',
                        'validate' => 'required',
                    ],
                ],
            ],
        ]);

        Blueprint::shouldReceive('find')
            ->with('forms.contact')
            ->andReturn($blueprint);

        $form = Form::make()->handle('contact');

        Form::shouldReceive('find')
            ->with('contact')
            ->andReturn($form);
    }

    private function clearSubmissions()
    {
        Form::find('contact')->submissions()->each->delete();
    }
}
