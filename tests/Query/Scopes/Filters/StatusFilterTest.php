<?php

namespace Tests\Query\Scopes\Filters;

use Illuminate\Support\Carbon;
use Mockery;
use Statamic\Facades\Collection;
use Statamic\Query\Scopes\Filters\Status;
use Statamic\Stache\Query\EntryQueryBuilder;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StatusFilterTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2013-01-01');
    }

    private function filter($query, $status)
    {
        (new Status)
            ->context(['collection' => 'test'])
            ->apply($query, ['status' => $status]);
    }

    /** @test */
    public function filters_by_draft()
    {
        $query = Mockery::mock(EntryQueryBuilder::class);
        $query->shouldReceive('where')->with('published', false)->once();

        $this->filter($query, 'draft');
    }

    /** @test */
    public function non_dated_collection_filters_by_published()
    {
        Collection::make('test')->save();

        $query = Mockery::mock(EntryQueryBuilder::class);
        $query->shouldReceive('where')->with('published', true)->once();

        $this->filter($query, 'published');
    }

    /** @test */
    public function future_private_dated_collection_filters_by_published()
    {
        Collection::make('test')->dated(true)->futureDateBehavior('private')->save();

        $query = Mockery::mock(EntryQueryBuilder::class);
        $query->shouldReceive('where')->with('published', true)->once();
        $query->shouldReceive('where')->withArgs(function ($arg1, $arg2, $arg3) {
            return $arg1 === 'date'
                && $arg2 === '<'
                && $arg3->eq(now());
        })->once();

        $this->filter($query, 'published');
    }

    /** @test */
    public function future_private_dated_collection_filters_by_scheduled()
    {
        Collection::make('test')->dated(true)->futureDateBehavior('private')->save();

        $query = Mockery::mock(EntryQueryBuilder::class);
        $query->shouldReceive('where')->with('published', true)->once();
        $query->shouldReceive('where')->withArgs(function ($arg1, $arg2, $arg3) {
            return $arg1 === 'date'
                && $arg2 === '>'
                && $arg3->eq(now());
        })->once();

        $this->filter($query, 'scheduled');
    }

    /** @test */
    public function past_private_dated_collection_filters_by_published()
    {
        Collection::make('test')->dated(true)->pastDateBehavior('private')->save();

        $query = Mockery::mock(EntryQueryBuilder::class);
        $query->shouldReceive('where')->with('published', true)->once();
        $query->shouldReceive('where')->withArgs(function ($arg1, $arg2, $arg3) {
            return $arg1 === 'date'
                && $arg2 === '>'
                && $arg3->eq(now());
        })->once();

        $this->filter($query, 'published');
    }

    /** @test */
    public function past_private_dated_collection_filters_by_expired()
    {
        Collection::make('test')->dated(true)->pastDateBehavior('private')->save();

        $query = Mockery::mock(EntryQueryBuilder::class);
        $query->shouldReceive('where')->with('published', true)->once();
        $query->shouldReceive('where')->withArgs(function ($arg1, $arg2, $arg3) {
            return $arg1 === 'date'
                && $arg2 === '<'
                && $arg3->eq(now());
        })->once();

        $this->filter($query, 'expired');
    }
}
