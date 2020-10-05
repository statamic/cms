<?php

namespace Tests\Feature\GlobalSearch;

use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Search;
use Statamic\Facades\User;
use Statamic\Search\Index;
use Statamic\Search\QueryBuilder;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_performs_a_global_search()
    {
        $entry = tap(
            Entry::make()
            ->id('1')->locale('en')->slug('test-entry')
            ->collection(tap(Collection::make('test-collection')->title('Test Collection'))->save())
        )->save();

        $results = collect([$entry]);

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('limit')->with(10)->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($results);

        $index = $this->mock(Index::class);
        $index->shouldReceive('ensureExists')->once()->andReturnSelf();
        $index->shouldReceive('search')->with('test')->once()->andReturn($builder);
        Search::shouldReceive('index')->once()->andReturn($index);

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get('/cp/search?q=test')
            ->assertOk()
            ->assertJsonStructure([
                [
                    'title', 'edit_url',
                    'collection', 'is_entry',
                    'taxonomy', 'is_term',
                    'container', 'is_asset',
                ],
            ]);
    }
}
