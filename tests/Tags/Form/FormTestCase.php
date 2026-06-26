<?php

namespace Tests\Tags\Form;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Support\Facades\Blade;
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

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false)->byDefault();

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
        return Parse::template($string, $context, trusted: true);
    }

    protected function blade($string, $context = [])
    {
        return Blade::render($string, $context);
    }

    protected function createForm($fieldContents = null, $handle = null)
    {
        $defaultFieldsContents = [
            'sections' => [
                ['fields' => $this->defaultFields],
            ],
        ];

        $handle = $handle ?? 'contact';

        $form = Form::make()
            ->handle($handle)
            ->honeypot('winnie')
            ->formFields($fieldContents ?? $defaultFieldsContents);

        Form::shouldReceive('find')->with($handle)->andReturn($form);
        Form::makePartial();
    }

    protected function createMultiPageForm($handle = 'survey')
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);

        $this->createForm([
            'pages' => [
                [
                    'id' => 'page_one',
                    'sections' => [
                        ['display' => 'Section A', 'fields' => [['handle' => 'name', 'field' => ['type' => 'text']]]],
                    ],
                ],
                [
                    'id' => 'page_two',
                    'sections' => [
                        ['display' => 'Section B', 'fields' => [['handle' => 'email', 'field' => ['type' => 'text']]]],
                    ],
                ],
            ],
        ], $handle);
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
