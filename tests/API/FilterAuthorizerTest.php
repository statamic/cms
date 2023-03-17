<?php

namespace Tests\API;

use Facades\Statamic\API\FilterAuthorizer;
use Statamic\Facades\Collection;
use Statamic\Facades\Config;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FilterAuthorizerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Collection::make('blog')->save();
        Collection::make('pages')->save();
    }

    public function configFileProvider()
    {
        return [
            ['api'],
            ['graphql'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function no_collection_entries_filters_are_allowed_by_default($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", false);

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'pages'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));

        Config::set("statamic.{$configFile}.resources.collections", true);

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'pages'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function it_allows_collection_entries_filters_when_configured_on_all_collections($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            '*' => [
                'allowed_filters' => ['title', 'slug'],
            ],
        ]);

        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'pages'));
        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function it_allows_collection_entries_filters_when_configured_on_specific_collections($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            'blog' => [
                'allowed_filters' => ['title', 'slug'],
            ],
        ]);

        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'pages'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));

        Config::set("statamic.{$configFile}.resources.collections", [
            'blog' => [
                'allowed_filters' => ['title', 'slug'],
            ],
            'pages' => [
                'allowed_filters' => ['title'],
            ],
        ]);

        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'pages'));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function it_merges_collection_entries_filters_with_all_collections_config($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            '*' => [
                'allowed_filters' => ['title'],
            ],
            'blog' => [
                'allowed_filters' => ['slug'],
            ],
        ]);

        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'pages'));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function it_allows_disabling_collection_entries_filters_on_specific_collections($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            '*' => [
                'allowed_filters' => ['title'],
            ],
            'blog' => [
                'allowed_filters' => false,
            ],
        ]);

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'pages'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function no_user_filters_are_allowed_by_default($configFile)
    {
        Config::set("statamic.{$configFile}.resources.users", false);

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForResource($configFile, 'users'));

        Config::set("statamic.{$configFile}.resources.users", true);

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForResource($configFile, 'users'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function it_allows_user_filters_when_configured($configFile)
    {
        Config::set("statamic.{$configFile}.resources.users.allowed_filters", [
            'name',
            'email',
        ]);

        $this->assertEqualsCanonicalizing(['name', 'email'], FilterAuthorizer::allowedForResource($configFile, 'users'));
    }
}
