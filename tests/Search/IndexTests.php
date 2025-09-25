<?php

namespace Tests\Search;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\SearchQueryPerformed;
use Statamic\Search\Index;

trait IndexTests
{
    #[Test]
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

    #[Test]
    public function it_respects_an_index_prefix()
    {
        $index = $this->getIndex();

        $originalName = $index->name();

        $prefix = 'foo';

        $index::resolveNameUsing(function ($name) use ($prefix) {
            return $prefix.'_'.$name;
        });

        $this->assertEquals($prefix.'_'.$originalName, $index->name());
    }

    public function tearDown(): void
    {
        // Reset the static state of the Index class
        Index::resolveNameUsing(null);
    }

    protected function beforeSearched()
    {
    }
}
