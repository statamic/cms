<?php

namespace Tests\API;

use Facades\Statamic\API\FilterAuthorizer;
use Statamic\Facades;
use Statamic\Facades\Config;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FilterAuthorizerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Facades\Collection::make('blog')->save();
        Facades\Collection::make('pages')->save();

        Facades\Nav::make('main')->save();
        Facades\Nav::make('footer')->save();

        Facades\Taxonomy::make('topics')->save();
        Facades\Taxonomy::make('tags')->save();

        Facades\AssetContainer::make('main')->save();
        Facades\AssetContainer::make('avatars')->save();

        $this->makeGlobalSet('branding')->save();
        $this->makeGlobalSet('socials')->save();

        Facades\Form::make('contact')->save();
        Facades\Form::make('newsletter')->save();
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
    public function no_sub_resource_filters_are_allowed_by_default_when_resource_is_disabled($configFile)
    {
        Config::set("statamic.{$configFile}.resources", [
            'collections' => false,
            'navs' => false,
            'taxonomies' => false,
            'assets' => false,
            'globals' => false,
            'forms' => false,
        ]);

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'navs', 'footer'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'navs', ['main', 'footer']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'navs', '*'));

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', 'tags'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', ['tags', 'topics']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', '*'));

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'assets', 'avatars'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'assets', ['main', 'avatars']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'assets', '*'));

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'globals', 'branding'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'globals', ['branding', 'socials']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'globals', '*'));

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'forms', 'contact'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'forms', ['contact', 'newsletter']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'forms', '*'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function no_sub_resource_filters_are_allowed_by_default_when_resource_is_enabled($configFile)
    {
        Config::set("statamic.{$configFile}.resources", [
            'collections' => true,
            'navs' => true,
            'taxonomies' => true,
            'assets' => true,
            'globals' => true,
            'forms' => true,
        ]);

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'navs', 'footer'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'navs', ['main', 'footer']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'navs', '*'));

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', 'tags'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', ['tags', 'topics']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', '*'));

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'assets', 'avatars'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'assets', ['main', 'avatars']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'assets', '*'));

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'globals', 'branding'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'globals', ['branding', 'socials']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'globals', '*'));

        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'forms', 'contact'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'forms', ['contact', 'newsletter']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'forms', '*'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function it_allows_filters_when_configured_using_wildcard_sub_resources($configFile)
    {
        Config::set("statamic.{$configFile}.resources", [
            'collections' => [
                '*' => ['allowed_filters' => ['title', 'slug']],
            ],
            'navs' => [
                '*' => ['allowed_filters' => ['title']],
            ],
            'taxonomies' => [
                '*' => ['allowed_filters' => ['title', 'color']],
            ],
            'assets' => [
                '*' => ['allowed_filters' => ['path']],
            ],
            'globals' => [
                '*' => ['allowed_filters' => ['site_name']],
            ],
            'forms' => [
                '*' => ['allowed_filters' => ['title']],
            ],
        ]);

        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));

        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'navs', 'footer'));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'navs', ['main', 'footer']));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'navs', '*'));

        $this->assertEqualsCanonicalizing(['title', 'color'], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', 'tags'));
        $this->assertEqualsCanonicalizing(['title', 'color'], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', ['tags', 'topics']));
        $this->assertEqualsCanonicalizing(['title', 'color'], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', '*'));

        $this->assertEqualsCanonicalizing(['path'], FilterAuthorizer::allowedForSubResources($configFile, 'assets', 'avatars'));
        $this->assertEqualsCanonicalizing(['path'], FilterAuthorizer::allowedForSubResources($configFile, 'assets', ['main', 'avatars']));
        $this->assertEqualsCanonicalizing(['path'], FilterAuthorizer::allowedForSubResources($configFile, 'assets', '*'));

        $this->assertEqualsCanonicalizing(['site_name'], FilterAuthorizer::allowedForSubResources($configFile, 'globals', 'branding'));
        $this->assertEqualsCanonicalizing(['site_name'], FilterAuthorizer::allowedForSubResources($configFile, 'globals', ['branding', 'socials']));
        $this->assertEqualsCanonicalizing(['site_name'], FilterAuthorizer::allowedForSubResources($configFile, 'globals', '*'));

        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'forms', 'contact'));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'forms', ['contact', 'newsletter']));
        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'forms', '*'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function it_allows_filters_when_configured_on_specific_sub_resources($configFile)
    {
        Config::set("statamic.{$configFile}.resources", [
            'collections' => [
                'blog' => ['allowed_filters' => ['title', 'slug']],
            ],
            'navs' => [
                'footer' => ['allowed_filters' => ['title']],
            ],
            'taxonomies' => [
                'tags' => ['allowed_filters' => ['title', 'color']],
            ],
            'assets' => [
                'avatars' => ['allowed_filters' => ['path']],
            ],
            'globals' => [
                'branding' => ['allowed_filters' => ['site_name']],
            ],
            'forms' => [
                'contact' => ['allowed_filters' => ['title']],
            ],
        ]);

        $this->assertEqualsCanonicalizing(['title', 'slug'], FilterAuthorizer::allowedForSubResources($configFile, 'collections', 'blog'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'collections', '*'));

        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'navs', 'footer'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'navs', ['main', 'footer']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'navs', '*'));

        $this->assertEqualsCanonicalizing(['title', 'color'], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', 'tags'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', ['tags', 'topics']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'taxonomies', '*'));

        $this->assertEqualsCanonicalizing(['path'], FilterAuthorizer::allowedForSubResources($configFile, 'assets', 'avatars'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'assets', ['main', 'avatars']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'assets', '*'));

        $this->assertEqualsCanonicalizing(['site_name'], FilterAuthorizer::allowedForSubResources($configFile, 'globals', 'branding'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'globals', ['branding', 'socials']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'globals', '*'));

        $this->assertEqualsCanonicalizing(['title'], FilterAuthorizer::allowedForSubResources($configFile, 'forms', 'contact'));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'forms', ['contact', 'newsletter']));
        $this->assertEqualsCanonicalizing([], FilterAuthorizer::allowedForSubResources($configFile, 'forms', '*'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function it_allows_filters_that_are_common_to_all_selected_sub_resources($configFile)
    {
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
    public function it_merges_sub_resources_filters_with_wildcard_sub_resources_config($configFile)
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
    public function it_allows_disabling_filters_on_specific_sub_resources_when_using_wildcard_config($configFile)
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

    private function makeGlobalSet($handle)
    {
        $set = Facades\GlobalSet::make()->handle($handle);

        $set->addLocalization(
            $set->makeLocalization('en')->data([])
        );

        return $set;
    }
}
