<?php

namespace Tests\Tags\Form;

use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\Parse;
use Statamic\Support\Arr;
use Statamic\Support\Html;
use Tests\NormalizesHtml;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

abstract class FormTestCase extends TestCase
{
    use PreventSavingStacheItemsToDisk, NormalizesHtml;

    protected $defaultFields = [
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
                'validate' => 'sometimes|required',
            ],
        ],
    ];

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

    public function post($uri, array $data = [], array $headers = [])
    {
        return parent::post($uri, $data, array_merge([
            'Content-Type' => 'multipart/form-data',
        ], $headers));
    }

    protected function tag($tag)
    {
        return Parse::template($tag, []);
    }

    protected function createContactForm($fields = null)
    {
        $blueprint = Blueprint::make()->setContents([
            'fields' => $fields ?? $this->defaultFields,
        ]);

        $handle = $fields ? $this->customFieldBlueprintHandle : 'contact';

        Blueprint::shouldReceive('find')->with("forms.{$handle}")->andReturn($blueprint);
        Blueprint::makePartial();

        $form = Form::make()->handle($handle)->honeypot('winnie');

        Form::shouldReceive('find')->with($handle)->andReturn($form);
        Form::makePartial();
    }

    protected function assertFieldRendersHtml($expectedHtmlParts, $fieldConfig, $oldData = [], $extraParams = [])
    {
        $randomString = str_shuffle('nobodymesseswiththehoff');

        $this->customFieldBlueprintHandle = $handle = $fieldConfig['handle'].'_'.$randomString;

        $fields = $oldData
            ? array_merge([['handle' => 'failing_field', 'field' => ['type' => 'text', 'validate' => 'required']]], [$fieldConfig])
            : [$fieldConfig];

        $this->createContactForm($fields);

        if ($oldData) {
            $this->post('/!/forms/'.$handle, $oldData)
                ->assertSessionHasErrors(['failing_field'], null, "form.{$handle}")
                ->assertLocation('/');
        }

        $extraParams = $extraParams ? Html::attributes($extraParams) : '';

        $output = $this->normalizeHtml(
            $this->tag("{{ form:{$handle} {$extraParams}}}{{ fields }}{{ field}}{{ /fields }}{{ /form:{$handle} }}", $oldData)
        );

        $expected = collect(Arr::wrap($expectedHtmlParts))->implode('');

        $this->assertStringContainsString($expected, $output);
    }

    protected function clearSubmissions()
    {
        Form::find('contact')->submissions()->each->delete();
    }
}
