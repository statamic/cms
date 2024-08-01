<?php

namespace Tests\Feature\Collections;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_edit_page_if_you_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);
        $user = User::make()->assignRole('test')->save();

        $collection = Collection::make('test')->save();

        $this
            ->actingAs($user)
            ->get(cp_route('collections.edit', $collection->handle()))
            ->assertSuccessful()
            ->assertViewHas('collection', $collection);
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $collection = Collection::make('test')->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('collections.edit', $collection->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }
}
