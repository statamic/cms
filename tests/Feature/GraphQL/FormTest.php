<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\GraphQL\CastableToValidationString;
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
        Form::make('contact')->title('Contact Us')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'name', 'field' => [
                            'type' => 'short_answer',
                            'display' => 'Your Name',
                            'instructions' => 'Enter your name',
                            'placeholder' => 'Type here...',
                            'invalid' => 'This isnt in the fieldtypes config fields so it shouldnt be output',
                            'width' => 50,
                        ]],
                        ['handle' => 'subject', 'field' => ['type' => 'select', 'options' => ['disco' => 'Disco', 'house' => 'House'], 'if' => ['name' => 'not empty']]],
                        ['handle' => 'message', 'field' => ['type' => 'long_answer', 'width' => 33, 'unless' => ['subject' => 'equals spam']]],
                    ],
                ],
            ],
        ])->save();

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
        Form::make('contact')->title('Contact Us')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer', 'validate' => ['required']]],
                        ['handle' => 'subject', 'field' => ['type' => 'select', 'options' => ['disco' => 'Disco', 'house' => 'House']]],
                        ['handle' => 'message', 'field' => ['type' => 'long_answer', 'validate' => ['required_if:select_field,disco']]],
                    ],
                ],
            ],
        ])->save();

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
        Form::make('contact')->title('Contact Us')->formFields([
            'sections' => [
                [
                    'display' => 'My Section',
                    'instructions' => 'The section instructions',
                    'fields' => [
                        ['handle' => 'name', 'field' => [
                            'type' => 'short_answer',
                            'display' => 'Your Name',
                            'instructions' => 'Enter your name',
                            'placeholder' => 'Type here...',
                            'invalid' => 'This isnt in the fieldtypes config fields so it shouldnt be output',
                            'width' => 50,
                        ]],
                        ['handle' => 'subject', 'field' => ['type' => 'select', 'options' => ['disco' => 'Disco', 'house' => 'House']]],
                        ['handle' => 'message', 'field' => ['type' => 'long_answer', 'width' => 33]],
                    ],
                ],
            ],
        ])->save();

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
        Form::make('contact')->title('Contact Us')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'name', 'field' => [
                            'type' => 'assets',
                            'display' => 'Asset',
                            'validate' => [
                                'mimes:image/jpeg,image/png',
                                'mimetypes:image/jpeg,image/png',
                                'dimensions:1024',
                                'size:1000',
                                'image:jpeg',
                                'new Tests\Feature\GraphQL\TestValidationRuleWithToString',
                                'new Tests\Feature\GraphQL\TestValidationRuleWithoutToString',
                            ],
                        ]],
                    ],
                ],
            ],
        ])->save();

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
                            'thevalidationrule:foo,bar',
                            'Tests\\Feature\\GraphQL\\TestValidationRuleWithoutToString::class',
                            'array',
                            'nullable',
                        ],
                    ],
                ],
            ]]);
    }
}

class TestValidationRuleWithToString implements CastableToValidationString
{
    public function toGqlValidationString(): string
    {
        return 'thevalidationrule:foo,bar';
    }
}

class TestValidationRuleWithoutToString
{
}
