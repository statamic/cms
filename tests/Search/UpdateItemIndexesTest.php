<?php

namespace Tests\Search;

use Mockery;
use Statamic\Contracts\Search\Searchable;
use Statamic\Events\UserDeleted;
use Statamic\Events\UserSaved;
use Statamic\Facades\Search;
use Statamic\Search\UpdateItemIndexes;
use Tests\TestCase;

class UpdateItemIndexesTest extends TestCase
{
    /** @test */
    public function it_updates_indexes_on_save()
    {
        $item = Mockery::mock(Searchable::class);

        Search::shouldReceive('updateWithinIndexes')->with($item)->once();

        $event = new UserSaved($item);

        $listener = new UpdateItemIndexes;

        $listener->update($event);
    }

    /** @test */
    public function it_updates_indexes_on_delete()
    {
        $item = Mockery::mock(Searchable::class);

        Search::shouldReceive('deleteFromIndexes')->with($item)->once();

        $event = new UserDeleted($item);

        $listener = new UpdateItemIndexes;

        $listener->delete($event);
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
