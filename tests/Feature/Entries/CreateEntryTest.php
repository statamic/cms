<?php

namespace Tests\Feature\Entries;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_entry_form()
    {
        $this->setTestRoles(['test' => ['access cp', 'create test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        Collection::make('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('collections.entries.create', ['test', 'en']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('entries/Create')
                ->where('values.title', null)
            );
    }

    #[Test]
    public function it_populates_the_form_from_query_string()
    {
        $this->setTestRoles(['test' => ['access cp', 'create test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        Collection::make('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('collections.entries.create', ['test', 'en']).'?values[title]=Foo Bar')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('entries/Create')
                ->where('values.title', 'Foo Bar')
            );
    }
}
