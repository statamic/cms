<?php

namespace Tests\Search;

use Facades\Tests\Factories\EntryFactory;
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

    /** @test */
    public function it_filters_entries_when_filter_is_defined()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
            'filter' => function ($entry) {
                return $entry->data()['searchable'];
            },
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $hiddenEntry = EntryFactory::collection('test')->slug('i-am-hidden')->data(['title' => 'I am hidden', 'searchable' => false])->make();
        $searchableEntry = EntryFactory::collection('test')->slug('find-me')->data(['title' => 'Find me', 'searchable' => true])->make();

        $this->assertFalse($index->filter($hiddenEntry));
        $this->assertTrue($index->filter($searchableEntry));
    }

    /** @test */
    public function it_filters_entries_when_no_filter_is_configured()
    {
        config()->set('statamic.search.indexes.default', [
            'fields' => [
                'title',
            ],
        ]);

        $index = app(\Statamic\Search\Comb\Index::class, [
            'name' => 'default',
            'config' => config('statamic.search.indexes.default'),
        ]);

        $hiddenEntry = EntryFactory::collection('test')->slug('i-am-hidden')->data(['title' => 'I am hidden', 'searchable' => false])->make();
        $searchableEntry = EntryFactory::collection('test')->slug('find-me')->data(['title' => 'Find me', 'searchable' => true])->make();

        $this->assertTrue($index->filter($hiddenEntry));
        $this->assertTrue($index->filter($searchableEntry));
    }

    protected function beforeSearched()
    {
    }
}
