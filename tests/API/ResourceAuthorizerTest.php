<?php

namespace Tests\API;

use Facades\Statamic\API\ResourceAuthorizer;
use Statamic\Facades\Collection;
use Statamic\Facades\Config;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ResourceAuthorizerTest extends TestCase
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
    public function no_collections_are_allowed_by_default($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", false);

        $this->assertFalse(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing([], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));

        Config::set("statamic.{$configFile}.resources.collections", []);

        $this->assertFalse(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing([], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function all_collections_are_allowed_when_setting_true_at_top_level($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", true);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing(['blog', 'pages'], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function wildcard_config_does_not_enable_any_collections_by_default($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            '*' => [
                'allowed_filters' => ['title', 'slug'],
            ],
        ]);

        $this->assertFalse(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing([], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function wildcard_config_can_enable_all_collections($configFile)
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

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function can_enable_individual_collections_via_boolean($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            'blog' => true,
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing(['blog'], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));
    }

    /**
     * @test
     *
     * @dataProvider configFileProvider
     */
    public function can_enable_individual_collections_via_array_config($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            'blog' => [
                'allowed_filters' => ['title'],
            ],
        ]);

        $this->assertTrue(ResourceAuthorizer::isAllowed($configFile, 'collections'));
        $this->assertEqualsCanonicalizing(['blog'], ResourceAuthorizer::allowedSubResources($configFile, 'collections'));
    }
}
