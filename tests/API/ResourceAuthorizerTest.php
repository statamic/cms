<?php

namespace Tests\API;

use Facades\Statamic\API\ResourceAuthorizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Config;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ResourceAuthorizerTest extends TestCase
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

    public static function configFileProvider()
    {
        return [
            ['api'],
            ['graphql'],
        ];
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function no_sub_resources_are_allowed_by_default($configFile)
    {
        Config::set("statamic.{$configFile}.resources", [
            'collections' => false,
            'navs' => false,
            'taxonomies' => false,
            'assets' => false,
            'globals' => false,
            'forms' => false,
        ]);

        $this->assertFalse(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing([], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));

        $this->assertFalse(ResourceAuthorizer::isAllowed($configFile, 'navs'));
        $this->assertEqualsCanonicalizing([], ResourceAuthorizer::allowedSubResources($configFile, 'navs'));

        $this->assertFalse(ResourceAuthorizer::isAllowed($configFile, 'taxonomies'));
        $this->assertEqualsCanonicalizing([], ResourceAuthorizer::allowedSubResources($configFile, 'taxonomies'));

        $this->assertFalse(ResourceAuthorizer::isAllowed($configFile, 'assets'));
        $this->assertEqualsCanonicalizing([], ResourceAuthorizer::allowedSubResources($configFile, 'assets'));

        $this->assertFalse(ResourceAuthorizer::isAllowed($configFile, 'globals'));
        $this->assertEqualsCanonicalizing([], ResourceAuthorizer::allowedSubResources($configFile, 'globals'));

        $this->assertFalse(ResourceAuthorizer::isAllowed($configFile, 'forms'));
        $this->assertEqualsCanonicalizing([], ResourceAuthorizer::allowedSubResources($configFile, 'forms'));
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function all_sub_resources_are_allowed_when_setting_true_at_top_level($configFile)
    {
        Config::set("statamic.{$configFile}.resources", [
            'collections' => true,
            'navs' => true,
            'taxonomies' => true,
            'assets' => true,
            'globals' => true,
            'forms' => true,
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing(['blog', 'pages'], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'navs'));
        $this->assertEqualsCanonicalizing(['main', 'footer'], ResourceAuthorizer::allowedSubResources($configFile, 'navs'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'taxonomies'));
        $this->assertEqualsCanonicalizing(['topics', 'tags'], ResourceAuthorizer::allowedSubResources($configFile, 'taxonomies'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'assets'));
        $this->assertEqualsCanonicalizing(['main', 'avatars'], ResourceAuthorizer::allowedSubResources($configFile, 'assets'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'globals'));
        $this->assertEqualsCanonicalizing(['branding', 'socials'], ResourceAuthorizer::allowedSubResources($configFile, 'globals'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'forms'));
        $this->assertEqualsCanonicalizing(['contact', 'newsletter'], ResourceAuthorizer::allowedSubResources($configFile, 'forms'));
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function wildcard_config_does_not_enable_sub_resource_by_default($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            '*' => [
                'allowed_filters' => ['title', 'slug'],
            ],
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing([], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function wildcard_config_can_enable_all_sub_resources($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            '*' => [
                'enabled' => true,
                'allowed_filters' => ['title', 'slug'],
            ],
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing(['blog', 'pages'], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function disabling_sub_resource_overrides_wildcard_config($configFile)
    {
        Facades\Collection::make('products')->save();

        Config::set("statamic.{$configFile}.resources.collections", [
            '*' => [
                'enabled' => true,
                'allowed_filters' => ['title', 'slug'],
            ],
            'pages' => false,
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing(['blog', 'products'], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function can_enable_individual_sub_resources_via_boolean($configFile)
    {
        Config::set("statamic.{$configFile}.resources", [
            'collections' => [
                'blog' => true,
            ],
            'navs' => [
                'footer' => true,
            ],
            'taxonomies' => [
                'topics' => true,
            ],
            'assets' => [
                'avatars' => true,
            ],
            'globals' => [
                'socials' => true,
            ],
            'forms' => [
                'contact' => true,
            ],
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing(['blog'], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'navs'));
        $this->assertEqualsCanonicalizing(['footer'], ResourceAuthorizer::allowedSubResources($configFile, 'navs'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'taxonomies'));
        $this->assertEqualsCanonicalizing(['topics'], ResourceAuthorizer::allowedSubResources($configFile, 'taxonomies'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'assets'));
        $this->assertEqualsCanonicalizing(['avatars'], ResourceAuthorizer::allowedSubResources($configFile, 'assets'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'globals'));
        $this->assertEqualsCanonicalizing(['socials'], ResourceAuthorizer::allowedSubResources($configFile, 'globals'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'forms'));
        $this->assertEqualsCanonicalizing(['contact'], ResourceAuthorizer::allowedSubResources($configFile, 'forms'));
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function can_enable_individual_sub_resources_via_array_values($configFile)
    {
        // We suggest enabling via booleans, as shown in above test, but still allow this for backwards compatibility
        Config::set("statamic.{$configFile}.resources", [
            'collections' => ['blog'],
            'navs' => ['footer'],
            'taxonomies' => ['topics'],
            'assets' => ['avatars'],
            'globals' => ['socials'],
            'forms' => ['contact'],
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing(['blog'], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'navs'));
        $this->assertEqualsCanonicalizing(['footer'], ResourceAuthorizer::allowedSubResources($configFile, 'navs'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'taxonomies'));
        $this->assertEqualsCanonicalizing(['topics'], ResourceAuthorizer::allowedSubResources($configFile, 'taxonomies'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'assets'));
        $this->assertEqualsCanonicalizing(['avatars'], ResourceAuthorizer::allowedSubResources($configFile, 'assets'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'globals'));
        $this->assertEqualsCanonicalizing(['socials'], ResourceAuthorizer::allowedSubResources($configFile, 'globals'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'forms'));
        $this->assertEqualsCanonicalizing(['contact'], ResourceAuthorizer::allowedSubResources($configFile, 'forms'));
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function can_enable_individual_sub_resources_via_array_config($configFile)
    {
        Config::set("statamic.{$configFile}.resources", [
            'collections' => [
                'blog' => ['allowed_filters' => ['title']],
            ],
            'navs' => [
                'footer' => ['allowed_filters' => ['title']],
            ],
            'taxonomies' => [
                'topics' => ['allowed_filters' => ['title']],
            ],
            'assets' => [
                'avatars' => ['allowed_filters' => ['title']],
            ],
            'globals' => [
                'socials' => ['allowed_filters' => ['title']],
            ],
            'forms' => [
                'contact' => ['allowed_filters' => ['title']],
            ],
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing(['blog'], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'navs'));
        $this->assertEqualsCanonicalizing(['footer'], ResourceAuthorizer::allowedSubResources($configFile, 'navs'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'taxonomies'));
        $this->assertEqualsCanonicalizing(['topics'], ResourceAuthorizer::allowedSubResources($configFile, 'taxonomies'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'assets'));
        $this->assertEqualsCanonicalizing(['avatars'], ResourceAuthorizer::allowedSubResources($configFile, 'assets'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'globals'));
        $this->assertEqualsCanonicalizing(['socials'], ResourceAuthorizer::allowedSubResources($configFile, 'globals'));

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'forms'));
        $this->assertEqualsCanonicalizing(['contact'], ResourceAuthorizer::allowedSubResources($configFile, 'forms'));
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function users_are_not_allowed_by_default($configFile)
    {
        Config::set("statamic.{$configFile}.resources.users", false);

        $this->assertFalse(ResourceAuthorizer::isAllowed($configFile, 'users'));
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function can_enable_users_via_boolean($configFile)
    {
        Config::set("statamic.{$configFile}.resources.users", true);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'users'));
    }

    #[Test]
    #[DataProvider('configFileProvider')]
    public function can_enable_users_via_array_config($configFile)
    {
        Config::set("statamic.{$configFile}.resources.users", [
            'allowed_filters' => ['title'],
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'users'));
    }

    #[Test]
    public function sites_are_not_allowed_by_default()
    {
        Config::set('statamic.graphql.resources.sites', false);

        $this->assertFalse(ResourceAuthorizer::isAllowed('graphql', 'sites'));
    }

    #[Test]
    public function can_enable_sitess_via_boolean()
    {
        Config::set('statamic.graphql.resources.sites', true);

        $this->assertTrue(ResourceAuthorizer::isAllowed('graphql', 'sites'));
    }

    #[Test]
    public function can_enable_sites_via_array_config()
    {
        Config::set('statamic.graphql.resources.sites', [
            'allowed_filters' => ['title'],
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed('graphql', 'sites'));
    }

    private function makeGlobalSet($handle)
    {
        $set = Facades\GlobalSet::make()->handle($handle);
        $set->save();

        $set->in('en')->data([])->save();

        return $set;
    }
}
