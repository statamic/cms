<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use Tests\TestCase;

/** @group graphql */
class SitesTest extends TestCase
{
    use EnablesQueries {
        getEnvironmentSetup as enableQueryEnvironmentSetup;
    }

    protected $enabledQueries = ['sites'];

    public function getEnvironmentSetUp($app)
    {
        $this->enableQueryEnvironmentSetup($app);

        $app['config']->set('statamic.sites', [
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);
    }

    /** @test */
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'sites')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'sites')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{entries}'])
            ->assertSee('Cannot query field \"entries\" on type \"Query\"', false);
    }

    /** @test */
    public function it_queries_global_sets()
    {
        $query = <<<'GQL'
{
    sites {
        handle
        name
        locale
        short_locale
        url
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'sites')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'sites')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['sites' => [
                ['handle' => 'en', 'name' => 'English', 'locale' => 'en_US', 'short_locale' => 'en', 'url' => 'http://test.com'],
                ['handle' => 'fr', 'name' => 'French', 'locale' => 'fr_FR', 'short_locale' => 'fr', 'url' => 'http://fr.test.com'],
                ['handle' => 'de', 'name' => 'German', 'locale' => 'de_DE', 'short_locale' => 'de', 'url' => 'http://test.com/de'],
            ]]]);
    }
}
