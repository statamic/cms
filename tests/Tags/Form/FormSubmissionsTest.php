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
    public function it_excludes_spam_and_partial_submissions_by_default()
    {
        $this->createSubmissions();

        $output = $this->tag('{{ form:submissions in="contact" }}{{ message }},{{ /form:submissions }}');

        $this->assertStringContainsString('finalized,', $output);
        $this->assertStringNotContainsString('partial,', $output);
        $this->assertStringNotContainsString('spam,', $output);
    }

    #[Test]
    public function it_includes_spam_submissions_when_the_status_is_queried()
    {
        $this->createSubmissions();

        $output = $this->tag('{{ form:submissions in="contact" status:is="spam" }}{{ message }},{{ /form:submissions }}');

        $this->assertStringContainsString('spam,', $output);
        $this->assertStringNotContainsString('finalized,', $output);
        $this->assertStringNotContainsString('partial,', $output);
    }

    #[Test]
    public function it_includes_all_submissions_when_any_status_is_queried()
    {
        $this->createSubmissions();

        $output = $this->tag('{{ form:submissions in="contact" status:is="any" }}{{ message }},{{ /form:submissions }}');

        $this->assertStringContainsString('finalized,', $output);
        $this->assertStringContainsString('partial,', $output);
        $this->assertStringContainsString('spam,', $output);
    }

    private function createSubmissions(): void
    {
        $form = Form::find('contact');

        $form->makeSubmission()->data(['message' => 'finalized'])->save();
        $form->makeSubmission()->data(['message' => 'partial'])->asPartial()->save();
        $form->makeSubmission()->data(['message' => 'spam'])->markAsSpam()->save();
    }
}
