<?php

namespace Search\Jobs;

use Mockery;
use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Index;
use Statamic\Search\IndexManager;
use Statamic\Search\Jobs\UpdateWithinIndexes;
use Statamic\Search\Search;
use Tests\TestCase;

class UpdateWithinIndexesTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider saveProvider
     */
    public function it_updates_indexes($updateMock)
    {
        $index = Mockery::mock(Index::class);
        $item = Mockery::mock(Searchable::class);

        $updateMock($index, $item);

        $indexes = Mockery::mock(IndexManager::class);
        $indexes->shouldReceive('all')->andReturn(collect([$index]));

        $search = new Search($indexes);

        $job = new UpdateWithinIndexes($item);
        $job->handle($search);
    }

    public function saveProvider()
    {
        return [
            'contains entry' => [
                function ($mock, $entry) {
                    $mock->shouldReceive('shouldIndex')->with($entry)->andReturnTrue();
                    $mock->shouldReceive('exists')->andReturnTrue();
                    $mock->shouldReceive('insert')->once()->with($entry);
                },
            ],

            'doesnt contain entry' => [
                function ($mock, $entry) {
                    $mock->shouldReceive('shouldIndex')->with($entry)->andReturnFalse();
                    $mock->shouldReceive('exists')->andReturnTrue();
                    $mock->shouldReceive('delete')->once();
                },
            ],

            'contains entry but index doesnt exist' => [
                function ($mock, $entry) {
                    $mock->shouldReceive('shouldIndex')->with($entry)->andReturnTrue();
                    $mock->shouldReceive('exists')->andReturnFalse();
                    $mock->shouldReceive('update')->once();
                },
            ],

            'doesnt contain entry and index doesnt exist' => [
                function ($mock, $entry) {
                    $mock->shouldReceive('shouldIndex')->with($entry)->andReturnFalse();
                    $mock->shouldReceive('exists')->once()->andReturnFalse();
                },
            ],
        ];
    }
}
