<?php

namespace Tests\Feature\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
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

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['test'],
            'query_scopes' => ['starts_with_c'],
        ]));

        $response = $this
            ->actingAs($user)
            ->get("/cp/fieldtypes/relationship?config={$config}&collections[0]=test")
            ->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->all();

        $this->assertCount(2, $titles);
        $this->assertContains('Carrot', $titles);
        $this->assertContains('Cherry', $titles);
        $this->assertNotContains('Apple', $titles);
        $this->assertNotContains('Banana', $titles);
    }
}

class StartsWithC extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', 'like', 'C%');
    }
}
