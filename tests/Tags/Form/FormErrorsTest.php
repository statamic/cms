<?php

namespace Tests\Tags\Form;

class FormErrorsTest extends FormTestCase
{
    /** @test */
    public function it_renders_errors()
    {
        $this
            ->post('/!/forms/contact')
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
}
