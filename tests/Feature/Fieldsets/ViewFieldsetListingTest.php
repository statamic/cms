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
            ->assertViewHas('fieldsets', collect(['My Fieldsets' => collect([
                [
                    'id' => 'foo',
                    'handle' => 'foo',
                    'title' => 'Foo',
                    'fields' => 0,
                    'edit_url' => 'http://localhost/cp/fields/fieldsets/foo/edit',
                    'delete_url' => 'http://localhost/cp/fields/fieldsets/foo',
                    'is_deletable' => true,
                ],
                [
                    'id' => 'bar',
                    'handle' => 'bar',
                    'title' => 'Bar',
                    'fields' => 0,
                    'edit_url' => 'http://localhost/cp/fields/fieldsets/bar/edit',
                    'delete_url' => 'http://localhost/cp/fields/fieldsets/bar',
                    'is_deletable' => true,
                ],
            ])]))
            ->assertDontSee('no-results');
    }

    /** @test */
    public function it_shows_a_list_of_editable_addon_fieldsets()
    {
        Facades\Fieldset::shouldReceive('all')->andReturn(collect([
            'foo' => $fieldsetA = $this->createfieldset('foo'),
            'baz::bar' => $fieldsetB = $this->createFieldset('baz::bar'),
        ]));

        $user = Facades\User::make()->makeSuper()->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('fieldsets.index'))
            ->assertSuccessful()
            ->assertViewHas('fieldsets', collect(
                [
                    'My Fieldsets' => collect([
                        [
                            'id' => 'foo',
                            'handle' => 'foo',
                            'title' => 'Foo',
                            'fields' => 0,
                            'edit_url' => 'http://localhost/cp/fields/fieldsets/foo/edit',
                            'delete_url' => 'http://localhost/cp/fields/fieldsets/foo',
                            'is_deletable' => true,
                        ],
                    ]),
                ],
                [
                    'Baz' => collect([
                        [
                            'id' => 'baz::bar',
                            'handle' => 'baz::bar',
                            'title' => 'Baz::bar',
                            'fields' => 0,
                            'edit_url' => 'http://localhost/cp/fields/fieldsets/baz::bar/edit',
                            'delete_url' => 'http://localhost/cp/fields/fieldsets/baz::bar',
                            'is_deletable' => false,
                        ],
                    ]),
                ]
            ));
    }

    /** @test */
    public function it_doesnt_show_non_editable_addon_fieldsets()
    {
        $fieldsetA = $this->createfieldset('foo::baz')->setContents(['editable' => false]);

        Facades\Fieldset::shouldReceive('all')->andReturn(collect([
            'foo::baz' => $fieldsetA,
            'baz::bar' => $fieldsetB = $this->createFieldset('baz::bar'),
        ]));

        $user = Facades\User::make()->makeSuper()->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('fieldsets.index'))
            ->assertSuccessful()
            ->assertViewHas('fieldsets', collect([
                [
                    'id' => 'baz::bar',
                    'handle' => 'baz::bar',
                    'title' => 'Baz::bar',
                    'fields' => 0,
                    'edit_url' => 'http://localhost/cp/fields/fieldsets/baz::bar/edit',
                    'delete_url' => 'http://localhost/cp/fields/fieldsets/baz::bar',
                ],
            ]));
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

    private function createFieldset($handle): Fieldset
    {
        return tap(new Fieldset)->setHandle($handle);
    }
}
