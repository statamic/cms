<?php

namespace Tests\Search\Typesense;

use Statamic\Search\Typesense\Index;
use Typesense\Client;

trait MakesTypesenseIndexes
{
    protected function makeTypesenseIndex(
        array $config = [],
        ?string $locale = null,
        string $name = 'test',
    ): array {
        $http = new FakeHttpClient;
        $client = new Client([
            'api_key' => 'admin-key',
            'nodes' => [
                [
                    'host' => 'typesense.test',
                    'port' => '8108',
                    'protocol' => 'http',
                ],
            ],
            'num_retries' => 0,
            'randomize_nodes' => false,
            'client' => $http,
        ]);

        $config = array_replace_recursive([
            'fields' => ['title'],
            'searchables' => [],
            'settings' => [
                'search_parameters' => [
                    'query_by' => 'title',
                ],
            ],
            'credentials' => [
                'api_key' => 'admin-key',
                'search_only_api_key' => 'search-key',
            ],
        ], $config);

        return [
            new Index($client, $name, $config, $locale),
            $http,
            $client,
        ];
    }
}
