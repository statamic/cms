<?php

namespace Tests\Search;

use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Index;
use Statamic\Search\IndexManager;
use Statamic\Search\Search;
use Tests\TestCase;

class SearchTest extends TestCase
{
    #[Test]
    #[DataProvider('saveProvider')]
    public function it_updates_indexes($updateMock)
    {
        $index = Mockery::mock(Index::class);
        $item = Mockery::mock(Searchable::class);

        $updateMock($index, $item);

        $indexes = Mockery::mock(IndexManager::class);
        $indexes->shouldReceive('all')->andReturn(collect([$index]));

        $search = new Search($indexes);

        $search->updateWithinIndexes($item);
    }

    public static function saveProvider()
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

    #[Test]
    #[DataProvider('deleteProvider')]
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

    public static function deleteProvider()
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
