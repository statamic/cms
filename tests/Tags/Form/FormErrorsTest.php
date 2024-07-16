<?php

namespace Tests\Tags\Form;

use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\FormSubmitted;

class FormErrorsTest extends FormTestCase
{
    #[Test]
    public function it_renders_errors()
    {
        $this
            ->post('/!/forms/contact', [
                'name' => null,
                'email' => null,
                'message' => null,
            ])
            ->assertSessionHasErrors(['email', 'message'], null, 'form.contact')
            ->assertLocation('/');

        $output = $this->tag(<<<'EOT'
{{ form:errors in="contact" }}
    <p class="error">{{ value }}</p>
{{ /form:errors }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);

        $expected = [
            'The Email Address field is required.',
            'The Message field is required.',
        ];

        $this->assertEquals($expected, $errors[1]);
    }

    #[Test]
    public function it_allows_use_of_sometimes_rule_for_conditionally_hidden_fields()
    {
        $this
            ->post('/!/forms/contact', [
                'name' => null,
                'email' => null,
                // 'message' => 'Has both the `sometimes` and `required` rule, so it should only validate if in then request.',
            ])
            ->assertSessionHasErrors(['email'], null, 'form.contact')
            ->assertSessionDoesntHaveErrors(['message'], null, 'form.contact')
            ->assertLocation('/');
    }

    #[Test]
    public function it_renders_errors_from_form_submitting_event()
    {
        Event::listen(FormSubmitted::class, function () {
            throw ValidationException::withMessages(['custom' => 'This is a custom message']);
        });

        $this
            ->post('/!/forms/contact', [
                'name' => 'name',
                'email' => 'test@test.com',
                'message' => 'message',
            ])
            ->assertSessionHasErrors(['custom'], null, 'form.contact')
            ->assertLocation('/');

        $output = $this->tag(<<<'EOT'
{{ form:errors in="contact" }}
    <p class="error">{{ value }}</p>
{{ /form:errors }}
EOT
        );

        preg_match_all('/<p class="error">(.+)<\/p>/U', $output, $errors);

        $expected = [
            'This is a custom message',
        ];

        $this->assertEquals($expected, $errors[1]);
    }
}
