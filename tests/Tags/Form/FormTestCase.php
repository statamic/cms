<?php

namespace Tests\Tags\Form;

use Illuminate\Support\Facades\Blade;
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
    use NormalizesHtml, PreventSavingStacheItemsToDisk;

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

    private $customFieldBlueprintHandle;

    public function setUp(): void
    {
        parent::setUp();

        $this->createForm();
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

    protected function tag($string, $context = [])
    {
        return Parse::template($string, $context);
    }

    protected function blade($string, $context = [])
    {
        return Blade::render($string, $context);
    }

    protected function createForm($blueprintContents = null, $handle = null)
    {
        $defaultBlueprintContents = [
            'fields' => $this->defaultFields,
        ];

        $blueprint = Blueprint::make()->setContents($blueprintContents ?? $defaultBlueprintContents);

        $handle = $handle ?? 'contact';

        Blueprint::shouldReceive('find')->with("forms.{$handle}")->andReturn($blueprint);
        Blueprint::makePartial();

        $form = Form::make()->handle($handle)->honeypot('winnie');

        Form::shouldReceive('find')->with($handle)->andReturn($form);
        Form::makePartial();
    }

    protected function assertFieldRendersHtml($expectedHtmlParts, $fieldConfig, $oldData = [], $extraParams = [])
    {
        $handle = str_shuffle('nobodymesseswiththehoff');

        $fields = $oldData
            ? array_merge([['handle' => 'failing_field', 'field' => ['type' => 'text', 'validate' => 'required']]], [$fieldConfig])
            : [$fieldConfig];

        $this->createForm(['fields' => $fields], $handle);

        if ($oldData) {
            $this->post('/!/forms/'.$handle, $oldData)
                ->assertSessionHasErrors(['failing_field'], null, "form.{$handle}")
                ->assertLocation('/');
        }

        $extraParams = $extraParams ? Html::attributes($extraParams) : '';

        $output = $this->normalizeHtml(
            $this->tag("{{ form:{$handle} {$extraParams}}}{{ form:fields }}{{ field }}{{ /form:fields }}{{ /form:{$handle} }}", $oldData)
        );

        $expected = collect(Arr::wrap($expectedHtmlParts))
            ->map(fn ($html) => str_replace('[[form-handle]]', $handle, $html)) // allow testing against dynamic form handle
            ->implode('');

        $this->assertStringContainsString($expected, $output);
    }

    protected function clearSubmissions()
    {
        Form::find('contact')->submissions()->each->delete();
    }
}
