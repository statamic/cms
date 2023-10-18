<?php

namespace Tests\Search;

use Mockery;
use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Index;
use Statamic\Search\IndexManager;
use Statamic\Search\Search;
use Tests\TestCase;

class SearchTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider deleteProvider
     */
    public function it_deletes_from_indexes($updateMock)
    {
        $index = Mockery::mock(Index::class);
        $item = Mockery::mock(Searchable::class);

        $updateMock($index, $item);

        $indexes = Mockery::mock(IndexManager::class);
        $indexes->shouldReceive('all')->andReturn(collect([$index]));

        $search = new Search($indexes);

        $search->deleteFromIndexes($item);
    }

    public function deleteProvider()
    {
        return [
            'index exists' => [
                function ($mock, $entry) {
                    $mock->shouldReceive('exists')->andReturnTrue();
                    $mock->shouldReceive('delete')->once();
                },
            ],

            'index doesnt exist' => [
                function ($mock, $entry) {
                    $mock->shouldReceive('exists')->andReturnFalse();
                    $mock->shouldReceive('delete')->never();
                },
            ],
        ];
    }
}
