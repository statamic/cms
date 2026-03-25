<?php

namespace Tests\Feature\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Query\Scopes\Scope;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RelationshipFieldtypeTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $collection;

    public function setUp(): void
    {
        parent::setUp();

        $this->collection = Collection::make('test')->save();

        app('statamic.scopes')[StartsWithC::handle()] = StartsWithC::class;
    }

    #[Test]
    public function it_filters_entries_by_query_scopes()
    {
        Entry::make()->collection('test')->slug('apple')->data(['title' => 'Apple'])->save();
        Entry::make()->collection('test')->slug('carrot')->data(['title' => 'Carrot'])->save();
        Entry::make()->collection('test')->slug('cherry')->data(['title' => 'Cherry'])->save();
        Entry::make()->collection('test')->slug('banana')->data(['title' => 'Banana'])->save();

        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['test'],
            'query_scopes' => ['starts_with_c'],
        ]));

        $response = $this
            ->actingAs($user)
            ->get("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->all();

        $this->assertCount(2, $titles);
        $this->assertContains('Carrot', $titles);
        $this->assertContains('Cherry', $titles);
        $this->assertNotContains('Apple', $titles);
        $this->assertNotContains('Banana', $titles);
    }

    #[Test]
    public function it_limits_access_to_entries_from_collections_the_user_can_view()
    {
        Collection::make('pages')->save();
        Entry::make()->collection('pages')->slug('home')->data(['title' => 'Home'])->save();

        Collection::make('secret')->save();
        Entry::make()->collection('secret')->slug('secret-one')->data(['title' => 'Secret One'])->save();

        $this->setTestRoles(['test' => ['access cp', 'view pages entries']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['pages', 'secret'],
        ]));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['slug' => 'home'],
                ],
            ]);
    }

    #[Test]
    public function it_denies_access_to_entries_when_user_cannot_view_any_of_the_collections()
    {
        Collection::make('pages')->save();
        Entry::make()->collection('pages')->slug('home')->data(['title' => 'Home'])->save();

        Collection::make('secret')->save();
        Entry::make()->collection('secret')->slug('secret-one')->data(['title' => 'Secret One'])->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['pages', 'secret'],
        ]));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertForbidden();
    }

    #[Test]
    public function it_forbids_access_to_entries_when_filters_target_collections_the_user_cannot_view()
    {
        Collection::make('secret')->save();
        Entry::make()->collection('test')->slug('apple')->data(['title' => 'Apple'])->save();
        Entry::make()->collection('secret')->slug('secret-one')->data(['title' => 'Secret One'])->save();

        $this->setTestRoles([
            'test' => ['access cp', 'view test entries'],
        ]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['test'],
        ]));
        $filters = base64_encode(json_encode([
            'collection' => ['collections' => ['secret']],
        ]));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}&filters={$filters}")
            ->assertForbidden();
    }

    #[Test]
    public function it_limits_access_to_terms_from_taxonomies_the_user_can_view()
    {
        Taxonomy::make('topics')->save();
        Taxonomy::make('secret')->save();
        Term::make('public')->taxonomy('topics')->data([])->save();
        Term::make('internal')->taxonomy('secret')->data([])->save();

        $this->setTestRoles(['test' => ['access cp', 'view topics terms']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'terms',
            'taxonomies' => ['topics', 'secret'],
        ]));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['slug' => 'public'],
                ],
            ]);
    }

    #[Test]
    public function it_forbids_access_to_terms_when_the_user_cannot_view_any_of_the_taxonomies()
    {
        Taxonomy::make('topics')->save();
        Taxonomy::make('secret')->save();
        Term::make('public')->taxonomy('topics')->data([])->save();
        Term::make('internal')->taxonomy('secret')->data([])->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'terms',
            'taxonomies' => ['topics', 'secret'],
        ]));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertForbidden();
    }
}

class StartsWithC extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', 'like', 'C%');
    }
}
