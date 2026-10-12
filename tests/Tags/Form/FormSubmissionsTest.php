<?php

namespace Tests\Tags\Form;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;

class FormSubmissionsTest extends FormTestCase
{
    #[Test]
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

    #[Test]
    public function it_only_renders_finalized_submissions_by_default()
    {
        $this->createSubmissions();

        $output = $this->tag('{{ form:submissions in="contact" }}[{{ message }}]{{ /form:submissions }}');

        $this->assertEquals('[finalized]', trim($output));
    }

    #[Test]
    public function it_renders_submissions_filtered_by_status()
    {
        $this->createSubmissions();

        $this->assertEquals('[partial]', trim($this->tag('{{ form:submissions in="contact" status:is="partial" }}[{{ message }}]{{ /form:submissions }}')));
        $this->assertEquals('[finalized]', trim($this->tag('{{ form:submissions in="contact" status:is="finalized" }}[{{ message }}]{{ /form:submissions }}')));
        $this->assertEquals('[finalized][partial]', trim($this->tag('{{ form:submissions in="contact" status:is="any" sort="message" }}[{{ message }}]{{ /form:submissions }}')));
    }

    private function createSubmissions()
    {
        $form = Form::find('contact');
        $form->makeSubmission()->data(['email' => 'san@holo.com', 'message' => 'finalized'])->save();
        $form->makeSubmission()->data(['email' => 'foba@bett.com', 'message' => 'partial'])->asPartial()->save();
    }
}
