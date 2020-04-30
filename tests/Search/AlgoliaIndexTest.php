<?php

namespace Tests\Search;

use Mockery;
use Statamic\Search\Algolia\Index;
use Statamic\Search\ItemResolver;
use Tests\TestCase;

class AlgoliaIndexTest extends TestCase
{
    use IndexTests;

    public function getIndex()
    {
        $resolver = Mockery::mock(ItemResolver::class);
        $resolver->shouldReceive('setIndex');

        $client = Mockery::mock(\AlgoliaSearch\Client::class);
        $index = Mockery::mock(\AlgoliaSearch\Index::class);

        $client->shouldReceive('initIndex')->andReturn($index);
        $index->shouldReceive('search')->andReturn(['hits' => []]);

        return new Index($resolver, $client);
    }
}
