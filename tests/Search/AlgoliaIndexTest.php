<?php

namespace Tests\Search;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Algolia\Index as AlgoliaIndex;
use Statamic\Search\Documents;
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
        $client = Mockery::mock(\Algolia\AlgoliaSearch\Api\SearchClient::class);

        return new AlgoliaIndex($client, $name, $config, $locale);
    }

    #[Test]
    public function it_only_checks_whether_an_existing_index_exists_once()
    {
        $client = Mockery::mock(SearchClient::class);
        $client->shouldReceive('listIndices')->once()->andReturn(['items' => [['name' => 'test']]]);
        $client->shouldNotReceive('setSettings');
        $client->shouldReceive('saveObjects')->times(3);

        $index = new AlgoliaIndex($client, 'test', ['settings' => ['hitsPerPage' => 20]], null);

        $index->insertDocuments(new Documents(collect(['one' => ['title' => 'One']])));
        $index->insertDocuments(new Documents(collect(['two' => ['title' => 'Two']])));
        $index->insertDocuments(new Documents(collect(['three' => ['title' => 'Three']])));
    }

    #[Test]
    public function it_applies_settings_once_when_the_index_does_not_exist()
    {
        $client = Mockery::mock(SearchClient::class);
        $client->shouldReceive('listIndices')->once()->andReturn(['items' => []]);
        $client->shouldReceive('setSettings')->once()->with('test', ['hitsPerPage' => 20]);
        $client->shouldReceive('saveObjects')->twice();

        $index = new AlgoliaIndex($client, 'test', ['settings' => ['hitsPerPage' => 20]], null);

        $index->insertDocuments(new Documents(collect(['one' => ['title' => 'One']])));
        $index->insertDocuments(new Documents(collect(['two' => ['title' => 'Two']])));
    }
}
