<?php

namespace Tests\CP;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Entries\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntriesTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_shows_entries_index()
    {
        $this->setTestRoles(['test' => [
            'access cp',
            'view test entries',
        ]]);

        $user = tap(User::make()->assignRole('test'))->save();

        $collection = tap(Collection::make('test'))->save();

        foreach (['one', 'two', 'three'] as $handle) {
            EntryFactory::collection($collection)
                ->slug($handle)
                ->create();
        }

        $response = $this
            ->actingAs($user)
            ->get(cp_route('collections.entries.index', ['collection' => 'test']))
            ->assertOk();

        $entries = collect($response->getData()->data);

        $this->assertEquals(['one', 'two', 'three'], $entries->pluck('slug')->all());
    }
}
