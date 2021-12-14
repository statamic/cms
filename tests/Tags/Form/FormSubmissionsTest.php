<?php

namespace Tests\Tags\Form;

class FormSubmissionsTest extends FormTestCase
{
    /** @test */
    public function it_renders_submissions()
    {
        $this
            ->post('/!/forms/contact', [
                'email' => 'san@holo.com',
                'message' => 'foo',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $this
            ->post('/!/forms/contact', [
                'email' => 'foba@bett.com',
                'message' => 'bar',
            ])
            ->assertSessionHasNoErrors()
            ->assertLocation('/');

        $output = $this->tag(<<<'EOT'
{{ form:submissions in="contact" }}
    <div>
        Email: {{ email }}<br>Message: {{ message }}
    </div>
{{ /form:submissions }}
EOT
        );

        $this->assertStringContainsString('Email: san@holo.com<br>Message: foo', $output);
        $this->assertStringContainsString('Email: foba@bett.com<br>Message: bar', $output);
    }
}
