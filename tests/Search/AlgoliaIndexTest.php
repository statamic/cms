<?php

namespace Tests\Search;

use Mockery;
use Statamic\Search\Algolia\Index as AlgoliaIndex;
use Tests\TestCase;

class AlgoliaIndexTest extends TestCase
{
    use IndexTests;

    public function getIndexClass()
    {
        return AlgoliaIndex::class;
    }

    public function getIndex($name, $config, $locale)
    {
        $client = Mockery::mock(\Algolia\AlgoliaSearch\SearchClient::class);

        return new AlgoliaIndex($client, $name, $config, $locale);
    }
}
