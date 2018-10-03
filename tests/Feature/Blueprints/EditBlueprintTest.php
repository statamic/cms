<?php

namespace Tests\Feature\Blueprint;

use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Fields\Blueprint;
use Tests\Fakes\FakeBlueprintRepository;
use Facades\Statamic\Fields\BlueprintRepository;

class EditBlueprintTest extends TestCase
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
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($blueprint->editUrl())
            ->assertRedirect('/original')
            ->assertSessionHasErrors();
    }

    /** @test */
    function it_provides_the_blueprint()
    {
        $this->withoutExceptionHandling();
        $user = API\User::create('test')->get()->makeSuper();
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->actingAs($user)
            ->get($blueprint->editUrl())
            ->assertStatus(200)
            ->assertViewHas('blueprint', $blueprint);
    }
}
