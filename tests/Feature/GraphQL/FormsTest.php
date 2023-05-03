<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\Form;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class FormsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use EnablesQueries;

    protected $enabledQueries = ['forms'];

    public function setUp(): void
    {
        parent::setUp();

        Form::all()->each->delete();
    }

    /**
     * @test
     *
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{forms}'])
            ->assertSee('Cannot query field \"forms\" on type \"Query\"', false);
    }

    /** @test */
    public function it_queries_forms()
    {
        Form::make('contact')->title('Contact Us')->save();
        Form::make('support')->title('Request Support')->save();

        $query = <<<'GQL'
{
    forms {
        handle
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['forms' => [
                ['handle' => 'contact', 'title' => 'Contact Us'],
                ['handle' => 'support', 'title' => 'Request Support'],
            ]]]);
    }
}
