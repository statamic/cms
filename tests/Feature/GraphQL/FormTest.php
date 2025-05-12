<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class FormTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['forms'];

    public function setUp(): void
    {
        parent::setUp();

        BlueprintRepository::partialMock();

        Form::all()->each->delete();
    }

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'forms')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'forms')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{form}'])
            ->assertSee('Cannot query field \"form\" on type \"Query\"', false);
    }

    #[Test]
    public function it_queries_a_form_by_handle()
    {
        Form::make('contact')->title('Contact Us')->save();
        Form::make('support')->title('Request Support')->honeypot('age')->save();

        $query = <<<'GQL'
{
    form(handle: "support") {
        handle
        title
        honeypot
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'forms')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'forms')->andReturn(Form::all()->map->handle()->all())->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'form' => [
                    'handle' => 'support',
                    'title' => 'Request Support',
                    'honeypot' => 'age',
                ],
            ]]);
    }

    #[Test]
    public function it_cannot_query_against_non_allowed_sub_resource()
    {
        $query = <<<'GQL'
{
    form(handle: "support") {
        handle
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'forms')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'forms')->andReturn([])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson([
                'errors' => [[
                    'message' => 'validation',
                    'extensions' => [
                        'validation' => [
                            'handle' => ['Forbidden: support'],
                        ],
                    ],
                ]],
                'data' => [
                    'form' => null,
                ],
            ]);
    }

    #[Test]
    public function it_queries_the_fields()
    {
        Form::make('contact')->title('Contact Us')->save();

        $blueprint = Blueprint::makeFromFields([
            'name' => [
                'type' => 'text',
                'display' => 'Your Name',
                'instructions' => 'Enter your name',
                'placeholder' => 'Type here...',
                'invalid' => 'This isnt in the fieldtypes config fields so it shouldnt be output',
                'width' => 50,
            ],
            'subject' => ['type' => 'select', 'options' => ['disco' => 'Disco', 'house' => 'House'], 'if' => ['name' => 'not empty']],
            'message' => ['type' => 'textarea', 'width' => 33, 'unless' => ['subject' => 'equals spam']],
        ]);

        BlueprintRepository::shouldReceive('find')->with('forms.contact')->andReturn($blueprint);

        $query = <<<'GQL'
{
    form(handle: "contact") {
        fields {
            handle
            type
            display
            instructions
            width
            config
            if
            unless
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'form' => [
                    'fields' => [
                        [
                            'handle' => 'name',
                            'type' => 'text',
                            'display' => 'Your Name',
                            'instructions' => 'Enter your name',
                            'width' => 50,
                            'config' => [
                                'placeholder' => 'Type here...',
                            ],
                            'if' => null,
                            'unless' => null,
                        ],
                        [
                            'handle' => 'subject',
                            'type' => 'select',
                            'display' => 'Subject',
                            'instructions' => null,
                            'width' => 100,
                            'config' => [
                                'options' => ['disco' => 'Disco', 'house' => 'House'],
                            ],
                            'if' => ['name' => 'not empty'],
                            'unless' => null,
                        ],
                        [
                            'handle' => 'message',
                            'type' => 'textarea',
                            'display' => 'Message',
                            'instructions' => null,
                            'width' => 33,
                            'config' => [],
                            'if' => null,
                            'unless' => ['subject' => 'equals spam'],
                        ],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_queries_the_validation_rules()
    {
        Form::make('contact')->title('Contact Us')->save();

        $blueprint = Blueprint::makeFromFields([
            'name' => ['type' => 'text', 'validate' => ['required']],
            'subject' => ['type' => 'select', 'options' => ['disco' => 'Disco', 'house' => 'House']],
            'message' => ['type' => 'textarea', 'validate' => ['required_if:select_field,disco']],
        ]);

        BlueprintRepository::shouldReceive('find')->with('forms.contact')->andReturn($blueprint);

        $query = <<<'GQL'
{
    form(handle: "contact") {
        rules
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'form' => [
                    'rules' => [
                        'name' => ['required'],
                        'subject' => ['nullable'],
                        'message' => ['required_if:select_field,disco'],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_queries_the_sections()
    {
        Form::make('contact')->title('Contact Us')->save();

        $blueprint = Blueprint::makeFromFields([
            'name' => [
                'type' => 'text',
                'display' => 'Your Name',
                'instructions' => 'Enter your name',
                'placeholder' => 'Type here...',
                'invalid' => 'This isnt in the fieldtypes config fields so it shouldnt be output',
                'width' => 50,
            ],
            'subject' => ['type' => 'select', 'options' => ['disco' => 'Disco', 'house' => 'House']],
            'message' => ['type' => 'textarea', 'width' => 33],
        ]);

        // Set section display and instructions. You wouldn't really do this for a form blueprint,
        // but this is just to test the section type which doesn't get tested anywhere else.
        $contents = $blueprint->contents();
        $contents['tabs']['main']['sections'][0]['display'] = 'My Section';
        $contents['tabs']['main']['sections'][0]['instructions'] = 'The section instructions';
        $blueprint->setContents($contents);

        BlueprintRepository::shouldReceive('find')->with('forms.contact')->andReturn($blueprint);

        $query = <<<'GQL'
{
    form(handle: "contact") {
        sections {
            display
            instructions
            fields {
                handle
                type
                display
                instructions
                width
                config
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'form' => [
                    'sections' => [
                        [
                            'display' => 'My Section',
                            'instructions' => 'The section instructions',
                            'fields' => [
                                [
                                    'handle' => 'name',
                                    'type' => 'text',
                                    'display' => 'Your Name',
                                    'instructions' => 'Enter your name',
                                    'width' => 50,
                                    'config' => [
                                        'placeholder' => 'Type here...',
                                    ],
                                ],
                                [
                                    'handle' => 'subject',
                                    'type' => 'select',
                                    'display' => 'Subject',
                                    'instructions' => null,
                                    'width' => 100,
                                    'config' => [
                                        'options' => ['disco' => 'Disco', 'house' => 'House'],
                                    ],
                                ],
                                [
                                    'handle' => 'message',
                                    'type' => 'textarea',
                                    'display' => 'Message',
                                    'instructions' => null,
                                    'width' => 33,
                                    'config' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_returns_string_based_validation_rules_for_mimes_mimetypes_dimension_size_and_image()
    {
        Form::make('contact')->title('Contact Us')->save();

        $blueprint = Blueprint::makeFromFields([
            'name' => [
                'type' => 'assets',
                'display' => 'Asset',
                'validate' => [
                    'mimes:image/jpeg,image/png',
                    'mimetypes:image/jpeg,image/png',
                    'dimensions:1024',
                    'size:1000',
                    'image:jpeg',
                ]
            ],
        ]);

        BlueprintRepository::shouldReceive('find')->with('forms.contact')->andReturn($blueprint);

        $query = <<<'GQL'
{
    form(handle: "contact") {
        rules
    }
}
GQL;
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'form' => [
                    'rules' => [
                        'name' => [
                            'mimes:image/jpeg,image/png',
                            'mimetypes:image/jpeg,image/png',
                            'dimensions:1024',
                            'size:1000',
                            'image:jpeg',
                            'array',
                            'nullable',
                        ],
                    ],
                ],
            ]]);
    }
}
