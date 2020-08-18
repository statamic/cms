<?php

namespace Tests\Feature\Fieldsets;

use Statamic\Facades;
use Statamic\Fields\Fieldset;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewFieldsetListingTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_shows_a_list_of_fieldsets()
    {
        Facades\Fieldset::shouldReceive('all')->andReturn(collect([
            'foo' => $fieldsetA = $this->createfieldset('foo'),
            'bar' => $fieldsetB = $this->createFieldset('bar'),
        ]));

        $user = Facades\User::make()->makeSuper()->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('fieldsets.index'))
            ->assertSuccessful()
            ->assertViewHas('fieldsets', collect([
                [
                    'id' => 'foo',
                    'handle' => 'foo',
                    'title' => 'Foo',
                    'fields' => 0,
                    'edit_url' => 'http://localhost/cp/fields/fieldsets/foo/edit',
                    'delete_url' => 'http://localhost/cp/fields/fieldsets/foo',
                ],
                [
                    'id' => 'bar',
                    'handle' => 'bar',
                    'title' => 'Bar',
                    'fields' => 0,
                    'edit_url' => 'http://localhost/cp/fields/fieldsets/bar/edit',
                    'delete_url' => 'http://localhost/cp/fields/fieldsets/bar',
                ],
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(Facades\User::make()->assignRole('test'))->save();

        $response = $this
            ->from('/cp/original')
            ->actingAs($user)
            ->get(cp_route('fieldsets.index'))
            ->assertRedirect('/cp/original');
    }

    private function createFieldset($handle)
    {
        return tap(new Fieldset)->setHandle($handle);
    }
}
