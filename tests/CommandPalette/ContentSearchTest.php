<?php

namespace Tests\CommandPalette;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Search;
use Statamic\Facades\User;
use Statamic\Search\Index;
use Statamic\Search\QueryBuilder;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ContentSearchTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_performs_a_global_search()
    {
        $entry1 = tap(
            Entry::make()
                ->id('1')->locale('en')->slug('test-entry-1')
                ->set('title', 'Entry 1')
                ->collection(tap(Collection::make('test-collection-1')->title('Test Collection 1'))->save())
        )->save();

        $entry2 = tap(
            Entry::make()
                ->id('2')->locale('en')->slug('test-entry-2')
                ->set('title', 'Entry 2')
                ->collection(tap(Collection::make('test-collection-2')->title('Test Collection 2'))->save())
        )->save();

        $results = collect([$entry1, $entry2])->map->toSearchResult();

        $builder = $this->mock(QueryBuilder::class);
        $builder->shouldReceive('get')->once()->andReturn($results);

        $index = $this->mock(Index::class);
        $index->shouldReceive('ensureExists')->once()->andReturnSelf();
        $index->shouldReceive('search')->with('test')->once()->andReturn($builder);
        $testUser = User::make()->assignRole('test')->save();
        Search::shouldReceive('index')->once()->andReturn($index);

        $this->setTestRoles(['test' => ['access cp', 'view test-collection-1 entries']]);
        $this
            ->actingAs($testUser)
            ->get('/cp/search?q=test')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonStructure([
                [
                    'title', 'url', 'badge', 'reference',
                ],
            ]);
    }
}
