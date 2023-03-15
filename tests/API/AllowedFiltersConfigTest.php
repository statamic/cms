<?php

namespace Tests\API;

use Facades\Statamic\API\AllowedFiltersConfig;
use Statamic\Facades\Collection;
use Statamic\Facades\Config;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AllowedFiltersConfigTest extends TestCase
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
     * @dataProvider configFileProvider
     */
    public function no_collection_entries_filters_are_allowed_by_default($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", false);

        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'blog'));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'pages'));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, '*'));

        Config::set("statamic.{$configFile}.resources.collections", true);

        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'blog'));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'pages'));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, '*'));
    }

    /**
     * @test
     * @dataProvider configFileProvider
     */
    public function it_allows_collection_entries_filters_when_configured_on_all_collections($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            '*' => [
                'allowed_filters' => ['title', 'slug'],
            ],
        ]);

        $this->assertEqualsCanonicalizing(['title', 'slug'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'blog'));
        $this->assertEqualsCanonicalizing(['title', 'slug'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'pages'));
        $this->assertEqualsCanonicalizing(['title', 'slug'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, ['blog', 'pages']));
        $this->assertEqualsCanonicalizing(['title', 'slug'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, '*'));
    }

    /**
     * @test
     * @dataProvider configFileProvider
     */
    public function it_allows_collection_entries_filters_when_configured_on_specific_collections($configFile)
    {
        Config::set("statamic.{$configFile}.resources.collections", [
            'blog' => [
                'allowed_filters' => ['title', 'slug'],
            ],
        ]);

        $this->assertEqualsCanonicalizing(['title', 'slug'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'blog'));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'pages'));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, '*'));

        Config::set("statamic.{$configFile}.resources.collections", [
            'blog' => [
                'allowed_filters' => ['title', 'slug'],
            ],
            'pages' => [
                'allowed_filters' => ['title'],
            ],
        ]);

        $this->assertEqualsCanonicalizing(['title', 'slug'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'blog'));
        $this->assertEqualsCanonicalizing(['title'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'pages'));
        $this->assertEqualsCanonicalizing(['title'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, ['blog', 'pages']));
        $this->assertEqualsCanonicalizing(['title'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, '*'));
    }

    /**
     * @test
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

        $this->assertEqualsCanonicalizing(['title', 'slug'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'blog'));
        $this->assertEqualsCanonicalizing(['title'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'pages'));
        $this->assertEqualsCanonicalizing(['title'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, ['blog', 'pages']));
        $this->assertEqualsCanonicalizing(['title'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, '*'));
    }

    /**
     * @test
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

        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'blog'));
        $this->assertEqualsCanonicalizing(['title'], AllowedFiltersConfig::allowedForCollectionEntries($configFile, 'pages'));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, ['blog', 'pages']));
        $this->assertEqualsCanonicalizing([], AllowedFiltersConfig::allowedForCollectionEntries($configFile, '*'));
    }
    }
}
