<?php

namespace Tests\Search;

use Illuminate\Support\Facades\Event;
use Statamic\Events\SearchQueryPerformed;

trait IndexTests
{
    /** @test */
    public function search_event_gets_emitted()
    {
        $this->markTestSkipped();

        Event::fake();

        $this->beforeSearched();

        $this->getIndex()->setName('test')->search('foo');

        Event::assertDispatched(SearchQueryPerformed::class, function ($event) {
            return $event->query === 'foo';
        });
    }

    protected function beforeSearched()
    {
    }
}
