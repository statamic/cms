<?php

namespace Statamic\GraphQL;

use Illuminate\Support\ServiceProvider as LaravelProvider;
use Statamic\GraphQL\Queries\AssetContainerQuery;
use Statamic\GraphQL\Queries\AssetContainersQuery;
use Statamic\GraphQL\Queries\AssetQuery;
use Statamic\GraphQL\Queries\AssetsQuery;
use Statamic\GraphQL\Queries\CollectionQuery;
use Statamic\GraphQL\Queries\CollectionsQuery;
use Statamic\GraphQL\Queries\EntriesQuery;
use Statamic\GraphQL\Queries\EntryQuery;
use Statamic\GraphQL\Queries\GlobalSetsQuery;
use Statamic\GraphQL\Queries\PingQuery;
use Statamic\GraphQL\Queries\TaxonomiesQuery;
use Statamic\GraphQL\Queries\TaxonomyQuery;
use Statamic\GraphQL\Queries\TermQuery;
use Statamic\GraphQL\Queries\TermsQuery;

class ServiceProvider extends LaravelProvider
{
    public function boot()
    {
        $this->app->booted(function () {
            $this->setDefaultSchema();
        });
    }

    private function setDefaultSchema()
    {
        $schema = [
            'query' => [
                'ping' => PingQuery::class,
                'entries' => EntriesQuery::class,
                'entry' => EntryQuery::class,
                'collections' => CollectionsQuery::class,
                'collection' => CollectionQuery::class,
                'assets' => AssetsQuery::class,
                'asset' => AssetQuery::class,
                'assetContainers' => AssetContainersQuery::class,
                'assetContainer' => AssetContainerQuery::class,
                'taxonomies' => TaxonomiesQuery::class,
                'taxonomy' => TaxonomyQuery::class,
                'terms' => TermsQuery::class,
                'term' => TermQuery::class,
                'globalSets' => GlobalSetsQuery::class,
            ],
            'mutation' => [],
            'middleware' => [],
            'method' => ['get', 'post'],
        ];

        config(['graphql.schemas.default' => $schema]);
    }
}
