<?php

namespace Statamic\GraphQL;

use Statamic\GraphQL\Queries\AssetContainerQuery;
use Statamic\GraphQL\Queries\AssetContainersQuery;
use Statamic\GraphQL\Queries\AssetQuery;
use Statamic\GraphQL\Queries\AssetsQuery;
use Statamic\GraphQL\Queries\CollectionQuery;
use Statamic\GraphQL\Queries\CollectionsQuery;
use Statamic\GraphQL\Queries\EntriesQuery;
use Statamic\GraphQL\Queries\EntryQuery;
use Statamic\GraphQL\Queries\GlobalSetQuery;
use Statamic\GraphQL\Queries\GlobalSetsQuery;
use Statamic\GraphQL\Queries\NavQuery;
use Statamic\GraphQL\Queries\NavsQuery;
use Statamic\GraphQL\Queries\PingQuery;
use Statamic\GraphQL\Queries\TaxonomiesQuery;
use Statamic\GraphQL\Queries\TaxonomyQuery;
use Statamic\GraphQL\Queries\TermQuery;
use Statamic\GraphQL\Queries\TermsQuery;

class DefaultSchema
{
    public static function config()
    {
        return [
            'query' => [
                PingQuery::class,
                EntriesQuery::class,
                EntryQuery::class,
                CollectionsQuery::class,
                CollectionQuery::class,
                AssetsQuery::class,
                AssetQuery::class,
                AssetContainersQuery::class,
                AssetContainerQuery::class,
                TaxonomiesQuery::class,
                TaxonomyQuery::class,
                TermsQuery::class,
                TermQuery::class,
                GlobalSetsQuery::class,
                GlobalSetQuery::class,
                NavsQuery::class,
                NavQuery::class,
            ],
            'mutation' => [],
            'middleware' => [],
            'method' => ['get', 'post'],
        ];
    }
}
