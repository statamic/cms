<?php

namespace Tests\Tags\Form;

class FormErrorsTest extends FormTestCase
{
    /** @test */
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

    /** @test */
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
}
