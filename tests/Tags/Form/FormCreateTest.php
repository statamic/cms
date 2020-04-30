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
            $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/forms">', $output);
            $this->assertStringContainsString('<input type="hidden" name="_token" value="">', $output);
            $this->assertStringEndsWith('</form>', $output);
        }
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
            ->with('contact')
            ->andReturn($blueprint);

        $form = Form::make()->handle('contact')->blueprint('contact');

        Form::shouldReceive('find')
            ->with('contact')
            ->andReturn($form);
    }
}
