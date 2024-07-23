<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class FormsTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['forms'];

    public function setUp(): void
    {
        parent::setUp();

        Form::make('contact')->title('Contact Us')->save();
        Form::make('support')->title('Request Support')->save();
    }

    public function tearDown(): void
    {
        Form::all()->each->delete();

        parent::tearDown();
    }

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'forms')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'forms')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{forms}'])
            ->assertSee('Cannot query field \"forms\" on type \"Query\"', false);
    }

    #[Test]
    public function it_queries_forms()
    {
        $query = <<<'GQL'
{
    forms {
        handle
        title
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
            ->assertExactJson(['data' => ['forms' => [
                ['handle' => 'contact', 'title' => 'Contact Us'],
                ['handle' => 'support', 'title' => 'Request Support'],
            ]]]);
    }

    #[Test]
    public function it_queries_only_allowed_sub_resources()
    {
        $query = <<<'GQL'
{
    forms {
        handle
        title
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'forms')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'forms')->andReturn(['contact'])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['forms' => [
                ['handle' => 'contact', 'title' => 'Contact Us'],
            ]]]);
    }
}
