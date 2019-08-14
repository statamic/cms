<?php

namespace Tests\Feature\Blueprint;

use Statamic\API;
use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Fields\Blueprint;
use Tests\Fakes\FakeBlueprintRepository;
use Tests\PreventSavingStacheItemsToDisk;
use Facades\Statamic\Fields\BlueprintRepository;

class EditBlueprintTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = API\User::make()->assignRole('test');
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($blueprint->editUrl())
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    function it_provides_the_blueprint()
    {
        $this->withoutExceptionHandling();
        $user = API\User::make()->makeSuper()->save();
        $blueprint = (new Blueprint)->setHandle('test')->setContents(['title' => 'Test'])->save();

        $this
            ->actingAs($user)
            ->get($blueprint->editUrl())
            ->assertStatus(200)
            ->assertViewHas('blueprint', $blueprint);
    }
}
