<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class FormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use EnablesQueries;

    protected $enabledQueries = ['forms'];

    public function setUp(): void
    {
        parent::setUp();

        BlueprintRepository::partialMock();

        Form::all()->each->delete();
    }

    /**
     * @test
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{form}'])
            ->assertSee('Cannot query field \"form\" on type \"Query\"', false);
    }

    /** @test */
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

    /** @test */
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
            ],
            'subject' => ['type' => 'select', 'options' => ['disco' => 'Disco', 'house' => 'House']],
            'message' => ['type' => 'textarea'],
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
            config
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
                            'config' => [
                                'placeholder' => 'Type here...',
                            ],
                        ],
                        [
                            'handle' => 'subject',
                            'type' => 'select',
                            'display' => 'Subject',
                            'instructions' => null,
                            'config' => [
                                'options' => ['disco' => 'Disco', 'house' => 'House'],
                            ],
                        ],
                        [
                            'handle' => 'message',
                            'type' => 'textarea',
                            'display' => 'Message',
                            'instructions' => null,
                            'config' => [],
                        ],
                    ],
                ],
            ]]);
    }
}
