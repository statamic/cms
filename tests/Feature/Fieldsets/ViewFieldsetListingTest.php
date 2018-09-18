<?php

namespace Tests\Feature\Fieldsets;

use Mockery;
use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Data\Users\User;
use Statamic\Fields\Fieldset;
use Statamic\Data\Entries\Collection;

class ViewFieldsetListingTest extends TestCase
{
    use FakesRoles;

    /** @test */
    function it_shows_a_list_of_fieldsets()
    {
        API\Fieldset::shouldReceive('all')->andReturn(collect([
            'foo' => $fieldsetA = $this->createfieldset('foo'),
            'bar' => $fieldsetB = $this->createFieldset('bar')
        ]));

        $user = API\User::create('test')->with(['super' => true])->get();

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
                    'edit_url' => 'http://localhost/cp/fieldsets/foo/edit'
                ],
                [
                    'id' => 'bar',
                    'handle' => 'bar',
                    'title' => 'Bar',
                    'fields' => 0,
                    'edit_url' => 'http://localhost/cp/fieldsets/bar/edit'
                ],
            ]))
            ->assertDontSee('no-results');
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::create('test')->get()->assignRole('test');

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
