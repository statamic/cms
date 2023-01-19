<?php

namespace Tests\Search;

use Mockery;
use Statamic\Contracts\Entries\Entry;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Search;
use Statamic\Search\Index;
use Statamic\Search\UpdateItemIndexes;
use Tests\TestCase;

class UpdateItemIndexesTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider saveProvider
     */
    public function it_updates_indexes_on_save($updateMock)
    {
        $index = Mockery::mock(Index::class);
        $item = Mockery::mock();

        $updateMock($index, $item);

        Search::shouldReceive('indexes')->andReturn(collect([$index]));

        $event = new EntrySaved($item);

        $listener = new UpdateItemIndexes;

        $listener->update($event);
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

    /**
     * @test
     *
     * @dataProvider deleteProvider
     */
    public function it_updates_indexes_on_delete($updateMock)
    {
        $index = Mockery::mock(Index::class);
        $entry = Mockery::mock(Entry::class);

        $updateMock($index, $entry);

        Search::shouldReceive('indexes')->andReturn(collect([$index]));

        $event = new EntryDeleted($entry);

        $listener = new UpdateItemIndexes;

        $listener->delete($event);
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

    /** @test */
    public function it_updates_term_localizations_when_saving_a_term()
    {
        $this->markTestIncomplete(); // todo
    }

    /** @test */
    public function it_deletes_term_localizations_when_deleting_a_term()
    {
        $this->markTestIncomplete(); // todo
    }
}
