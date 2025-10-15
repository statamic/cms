<?php

namespace Tests\Search;

use Mockery;
use Statamic\Search\Algolia\Index as AlgoliaIndex;
use Tests\TestCase;

class AlgoliaIndexTest extends TestCase
{
    use IndexTests;

    public function getIndex($name)
    {
        $client = Mockery::mock(\Algolia\AlgoliaSearch\SearchClient::class);

        return new AlgoliaIndex($client, $name, [], null);
    }
}
