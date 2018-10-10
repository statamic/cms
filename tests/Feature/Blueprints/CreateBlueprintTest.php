<?php

namespace Tests\Feature\Blueprints;

use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;
use Tests\Fakes\FakeBlueprintRepository;
use Facades\Statamic\Fields\BlueprintRepository;

class CreateBlueprintTest extends TestCase
{
    use FakesRoles;

    protected function setUp()
    {
        parent::setUp();

        BlueprintRepository::swap(new FakeBlueprintRepository);
    }

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::create('test')->get()->assignRole('test');

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('blueprints.create'))
            ->assertRedirect('/original')
            ->assertSessionHasErrors();
    }
}
