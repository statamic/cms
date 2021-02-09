<?php

namespace Tests\Feature\GraphQL;

use Tests\TestCase;

/** @group graphql */
class SitesTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

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
