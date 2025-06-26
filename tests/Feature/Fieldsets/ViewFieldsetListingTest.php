<?php

namespace Tests\Feature\Fieldsets;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Fields\Fieldset;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewFieldsetListingTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_a_list_of_fieldsets()
    {
        Facades\Fieldset::shouldReceive('all')->andReturn(collect([
            'foo' => $fieldsetA = $this->createfieldset('foo'),
            'bar' => $fieldsetB = $this->createFieldset('bar'),
            'baz::foo' => $this->createFieldset('baz::foo'),
            'baz::bar' => $this->createFieldset('baz::bar'),
            'baz::baz' => $this->createFieldset('baz::baz'),
        ]));

        Facades\Fieldset::shouldReceive('overriddenNamespacedFieldsetPath')
            ->with('baz::foo')
            ->andReturn('/fieldsets/vendor/baz/foo.yaml');

        Facades\Fieldset::shouldReceive('overriddenNamespacedFieldsetPath')
            ->with('baz::bar')
            ->andReturn('/fieldsets/vendor/baz/bar.yaml');

        // Custom policy to allow fieldsets to demonstrate how certain fieldset can be restricted
        app()->bind(\Statamic\Policies\FieldsetPolicy::class, function () {
            return new class extends \Statamic\Policies\FieldsetPolicy
            {
                public function before($user, $ability, $fieldset)
                {
                    return $fieldset->handle() === 'baz::baz'
                        ? false
                        : parent::before($user, $ability, $fieldset);
                }
            };
        });

        $this->setTestRoles(['test' => ['access cp', 'configure fields']]);
        $user = Facades\User::make()->assignRole('test')->save();

        $response = $this
            ->actingAs($user)
            ->get(cp_route('fieldsets.index'))
            ->assertSuccessful()
            ->assertViewHas('fieldsets', collect([
                'My Fieldsets' => collect([
                    [
                        'id' => 'foo',
                        'handle' => 'foo',
                        'title' => 'Foo',
                        'fields' => 0,
                        'edit_url' => 'http://localhost/cp/fields/fieldsets/foo/edit',
                    ],
                    [
                        'id' => 'bar',
                        'handle' => 'bar',
                        'title' => 'Bar',
                        'fields' => 0,
                        'edit_url' => 'http://localhost/cp/fields/fieldsets/bar/edit',
                    ],
                ]),
                'Baz' => collect([
                    [
                        'id' => 'baz::foo',
                        'handle' => 'baz::foo',
                        'title' => 'Foo',
                        'fields' => 0,
                        'edit_url' => 'http://localhost/cp/fields/fieldsets/baz::foo/edit',
                    ],
                    [
                        'id' => 'baz::bar',
                        'handle' => 'baz::bar',
                        'title' => 'Bar',
                        'fields' => 0,
                        'edit_url' => 'http://localhost/cp/fields/fieldsets/baz::bar/edit',
                    ],
                ]),
            ]))
            ->assertDontSee('no-results');
    }

    #[Test]
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
