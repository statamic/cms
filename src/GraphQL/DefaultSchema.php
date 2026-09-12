<?php

namespace Statamic\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use Rebing\GraphQL\Support\Contracts\ConfigConvertible;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\GraphQL\Middleware\CacheResponse;
use Statamic\GraphQL\Middleware\HandleAuthentication;
use Statamic\GraphQL\Queries\AssetContainerQuery;
use Statamic\GraphQL\Queries\AssetContainersQuery;
use Statamic\GraphQL\Queries\AssetQuery;
use Statamic\GraphQL\Queries\AssetsQuery;
use Statamic\GraphQL\Queries\CollectionQuery;
use Statamic\GraphQL\Queries\CollectionsQuery;
use Statamic\GraphQL\Queries\EntriesQuery;
use Statamic\GraphQL\Queries\EntryQuery;
use Statamic\GraphQL\Queries\FormQuery;
use Statamic\GraphQL\Queries\FormsQuery;
use Statamic\GraphQL\Queries\GlobalSetQuery;
use Statamic\GraphQL\Queries\GlobalSetsQuery;
use Statamic\GraphQL\Queries\NavQuery;
use Statamic\GraphQL\Queries\NavsQuery;
use Statamic\GraphQL\Queries\PingQuery;
use Statamic\GraphQL\Queries\SitesQuery;
use Statamic\GraphQL\Queries\SpecificEntriesQuery;
use Statamic\GraphQL\Queries\SpecificEntryQuery;
use Statamic\GraphQL\Queries\SpecificTermQuery;
use Statamic\GraphQL\Queries\SpecificTermsQuery;
use Statamic\GraphQL\Queries\TaxonomiesQuery;
use Statamic\GraphQL\Queries\TaxonomyQuery;
use Statamic\GraphQL\Queries\TermQuery;
use Statamic\GraphQL\Queries\TermsQuery;
use Statamic\GraphQL\Queries\UserQuery;
use Statamic\GraphQL\Queries\UsersQuery;

class DefaultSchema implements ConfigConvertible
{
    public function toConfig(): array
    {
        return $this->getConfig();
    }

    public static function config()
    {
        return app(self::class)->getConfig();
    }

    public function getConfig()
    {
        return [
            'query' => $this->getQueries(),
            'mutation' => $this->getMutations(),
            'middleware' => $this->getMiddleware(),
            'method' => ['GET', 'POST'],
        ];
    }

    private function getQueries()
    {
        $queries = collect([PingQuery::class]);
        $allowed = [];

        collect([
            'collections' => [CollectionsQuery::class, CollectionQuery::class, EntriesQuery::class, EntryQuery::class],
            'assets' => [AssetContainersQuery::class, AssetContainerQuery::class, AssetsQuery::class, AssetQuery::class],
            'taxonomies' => [TaxonomiesQuery::class, TaxonomyQuery::class, TermsQuery::class, TermQuery::class],
            'globals' => [GlobalSetsQuery::class, GlobalSetQuery::class],
            'navs' => [NavsQuery::class, NavQuery::class],
            'forms' => [FormsQuery::class, FormQuery::class],
            'sites' => [SitesQuery::class],
            'users' => [UsersQuery::class, UserQuery::class],
        ])->each(function ($qs, $resource) use (&$queries, &$allowed) {
            $allowed[$resource] = ResourceAuthorizer::isAllowed('graphql', $resource);
            $queries = $queries->merge($allowed[$resource] ? $qs : []);
        });

        if ($allowed['collections'] ?? false) {
            $queries = $queries->merge($this->getSpecificEntriesQueries());
        }

        if ($allowed['taxonomies'] ?? false) {
            $queries = $queries->merge($this->getSpecificTermsQueries());
        }

        return $queries
            ->merge(config('statamic.graphql.queries', []))
            ->merge(GraphQL::getExtraQueries())
            ->all();
    }

    private function getSpecificEntriesQueries(): array
    {
        // rebing/graphql-laravel calls toConfig() eagerly during boot
        // at which point the Stache is not yet ready.
        // The schema is rebuilt when an actual request hits the controller,
        // where the Stache is fully booted, so wildcards still expand correctly there.
        if (! app()->isBooted()) {
            return [];
        }

        $configured = config('statamic.graphql.improved_types.collections', []);

        if (empty($configured)) {
            return [];
        }

        if (in_array('*', $configured)) {
            $handles = Collection::handles()->all();
        } else {
            $handles = $configured;
        }

        $allowed = ResourceAuthorizer::allowedSubResources('graphql', 'collections');

        return collect($handles)
            ->filter(fn ($handle) => in_array($handle, $allowed))
            ->flatMap(fn ($handle) => [
                new SpecificEntriesQuery($handle),
                new SpecificEntryQuery($handle),
            ])
            ->all();
    }

    private function getSpecificTermsQueries(): array
    {
        if (! app()->isBooted()) {
            return [];
        }

        $configured = config('statamic.graphql.improved_types.terms', []);

        if (empty($configured)) {
            return [];
        }

        if (in_array('*', $configured)) {
            $handles = Taxonomy::handles()->all();
        } else {
            $handles = $configured;
        }

        $allowed = ResourceAuthorizer::allowedSubResources('graphql', 'taxonomies');

        return collect($handles)
            ->filter(fn ($handle) => in_array($handle, $allowed))
            ->flatMap(fn ($handle) => [
                new SpecificTermsQuery($handle),
                new SpecificTermQuery($handle),
            ])
            ->all();
    }

    private function getMiddleware()
    {
        return array_merge(
            [HandleAuthentication::class, CacheResponse::class],
            config('statamic.graphql.middleware', []),
            GraphQL::getExtraMiddleware()
        );
    }

    private function getMutations()
    {
        return config('statamic.graphql.mutations', []);
    }
}
