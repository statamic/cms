<?php

namespace Statamic\GraphQL;

use Illuminate\Support\ServiceProvider as LaravelProvider;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\GraphQL\Queries\EntriesQuery;
use Statamic\GraphQL\Queries\EntryQuery;
use Statamic\GraphQL\Queries\PingQuery;
use Statamic\GraphQL\Types\EntryInterface;

class ServiceProvider extends LaravelProvider
{
    public function boot()
    {
        $this->app->booted(function () {
            $this->setDefaultSchema();
            $this->enableLazyLoading();
            $this->addTypes();
        });
    }

    private function setDefaultSchema()
    {
        $schema = [
            'query' => [
                'ping' => PingQuery::class,
                'entries' => EntriesQuery::class,
                'entry' => EntryQuery::class,
            ],
            'mutation' => [],
            'middleware' => [],
            'method' => ['get', 'post'],
        ];

        config(['graphql.schemas.default' => $schema]);
    }

    private function enableLazyLoading()
    {
        config(['graphql.lazyload_types' => true]);
    }

    private function addTypes()
    {
        GraphQL::addType(EntryInterface::class);
    }
}
