<?php

namespace Tests\Feature\Fieldsets;

use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Fields\Fieldset;
use Tests\Fakes\FakeFieldsetRepository;
use Facades\Statamic\Fields\FieldsetRepository;

class EditFieldsetTest extends TestCase
{
    use FakesRoles;

    protected function setUp()
    {
        parent::setUp();

        FieldsetRepository::swap(new FakeFieldsetRepository);
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::create('test')->get()->assignRole('test');
        $fieldset = (new Fieldset)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($fieldset->editUrl())
            ->assertRedirect('/original')
            ->assertSessionHasErrors();
    }

    /** @test */
    function it_provides_the_fieldset()
    {
        $this->withoutExceptionHandling();
        $user = API\User::create('test')->get()->makeSuper();
        $fieldset = (new Fieldset)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->actingAs($user)
            ->get($fieldset->editUrl())
            ->assertStatus(200)
            ->assertViewHas('fieldset', $fieldset);
    }
}
